<?php

namespace Elbo\Library;

use GuzzleHttp\{Client, TransferStats};
use Elbo\{Models\DomainPolicy, Exceptions\UnsafeURLException};

class URLInfoCollector {
	protected $client;
	protected $deny_regex;
	protected $policy_cache = [];

	public function __construct(Configuration $config) {
		$deny_regex = $config->get('url_policies.deny_urls', null);

		if ($deny_regex === null || $deny_regex === "") {
			$this->deny_regex = null;
		}
		else {
			$this->deny_regex = '/'.str_replace('/', '\/', $deny_regex).'/';
		}

		$this->client = new Client([
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063',
				'Accept' => 'text/html, application/xhtml+xml, image/jxr, */*',
				'Accept-Encoding' => 'gzip, deflate',
				'Accept-Language' => 'en-US',
				'DNT' => '1'
			],
			'allow_redirects' => [
				'referer' => true,
				'max_redirects' => 4
			],
			'cookies' => true
		]);
	}

	protected function isHostAllowed($host) {
		# If the cache has been used for quite a few entries, clean it up.
		if (count($this->policy_cache) > 20) {
			$this->policy_cache = [];
		}

		if (!isset($this->policy_cache[$host])) {
			$this->policy_cache[$host] = DomainPolicy::isAllowed($host);
		}

		return $this->policy_cache[$host];
	}

	protected function isURLSafe($url) {
		$host = $url->getHost();

		if (!$this->isHostAllowed($host)) {
			return false;
		}

		if ($this->deny_regex === null) {
			return true;
		}

		return preg_match($this->deny_regex, (string)$url) !== 1;
	}

	protected static function strip($str) {
		return preg_replace('/\s+/', ' ', trim($str));
	}

	protected static function getTitle(string $str) {
		if (!preg_match('/<\s*title[^>]*>([^<]+)/m', $str, $matches)) {
			return null;
		}

		$title = self::strip(html_entity_decode($matches[1], ENT_QUOTES));

		# The title won't be usable if it's empty or non-UTF8
		if ($title === '' || !mb_check_encoding($title, "UTF-8")) {
			return null;
		}

		return mb_substr($title, 0, 100);
	}

	protected static function getMetaRedirectHost(string $str) {
		if (preg_match('~<\s*meta\s+[^>]*http-equiv\s*=\s*["\']?\s*refresh\s*["\']?\s+[^>]*content\s*=\s*["\']?\s*\d+\s*;\s*url\s*=\s*https?://([^"\'/\s]+)(:[0-9]+)?~i', $str, $matches)) {
			return $matches[1];
		}

		return null;
	}

	public function getInfo(URL $url) {
		if (!$this->isURLSafe($url)) {
			throw new UnsafeURLException();
		}

		$initial_url_traversed = false;

		try {
			$response = $this->client->request('GET', (string)$url, [
				'stream' => true,
				'timeout' => 5,
				'connect_timeout' => 5,
				'on_stats' => function(TransferStats $stats) use (&$initial_url_traversed) {
					if ($initial_url_traversed) {
						if (!$this->isURLSafe($stats->getEffectiveUri())) {
							throw new UnsafeURLException();
						}
					}
					else {
						$initial_url_traversed = true;
					}
				}
			]);

			$body = $response->getBody();
			$response = '';
			$response_len = 0;

			while (!$body->eof() && $response_len <= 32767) {
				$tmp = $body->read(1024);
				$response_len += strlen($tmp);
				$response .= $tmp;
			}

			$body->close();
			$meta_redirect_host = self::getMetaRedirectHost($response);

			if ($meta_redirect_host !== null && !$this->isHostAllowed($meta_redirect_host)) {
				throw new UnsafeURLException();
			}

			$title = $this->getTitle($response);
		}
		catch (\RuntimeException $e) {
			# We couldn't get the contents of the URL; nothing requires to be done.
			# Guzzle usually throws a GuzzleHttp\Exception\TransferException or one
			# of its derived exceptions, but in some rare cases a RuntimeException
			# can also be thrown.
		}

		return [
			'title' => $title ?? null
		];
	}
}