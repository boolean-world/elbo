<?php

namespace Elbo\Library;

use Elbo\{Models\DomainPolicy, Exceptions\UnsafeURLException};
use GuzzleHttp\{Client, TransferStats, Psr7\Uri, Psr7\UriResolver};

class URLInfoCollector {
	const max_redirects = 5;

	protected $client;
	protected $deny_regex;
	protected $policy_cache = [];

	public function __construct(Configuration $config) {
		$deny_regex = $config->get('url_policies.deny_urls', null);

		if ($deny_regex === null || $deny_regex === "") {
			$this->deny_regex = null;
		}
		else {
			$this->deny_regex = '/'.str_replace('/', '\/', $deny_regex).'/i';
		}

		$this->client = new Client([
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063',
				'Accept' => 'text/html, application/xhtml+xml, image/jxr, */*',
				'Accept-Encoding' => 'gzip, deflate',
				'Accept-Language' => 'en-US',
				'DNT' => '1'
			],
			'allow_redirects' => false,
			'exceptions' => false,
			'cookies' => true
		]);
	}

	protected function isHostAllowed($host) {
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

	protected static function stripHTMLSpaces($str) {
		static $find = ['/\s*<\s*/', '/\s*>\s*/', '/\s+/', '/<noscript[^>]*>.*?<\/noscript>/'];
		static $replace = ['<', '>', ' '];

		return preg_replace($find, $replace, trim($str));
	}

	protected static function getTitle(string $str) {
		if (!preg_match('/<\s*title[^>]*>([^<]+)/m', $str, $matches)) {
			return null;
		}

		$title = html_entity_decode($matches[1], ENT_QUOTES);

		# The title won't be usable if it's empty or non-UTF8
		if ($title === '' || !mb_check_encoding($title, "UTF-8")) {
			return null;
		}

		return mb_substr($title, 0, 100);
	}

	protected static function getClientRedirect(string $str) {
		if (preg_match('/<meta http-equiv=[^>]+ content=[^>]+url=([^"\'>]+)/i', $str, $matches)) {
			return $matches[1];
		}

		if (preg_match('/
			(location(\.href)?\s*=|location\.replace\s*\()\s*["\']([^"\']+)
		/x', substr($str, 0, 512), $matches)) {
			return $matches[1];
		}

		return null;
	}

	public function getInfo(URL $url) {
		$url = new Uri((string)$url);
		$referer = '';

		try {
			for ($i = 0; $i < self::max_redirects; $i++) {
				if (!$this->isURLSafe($url)) {
					throw new UnsafeURLException('Blacklisted domain.');
				}

				$response = $this->client->request('GET', $url, [
					'stream' => true,
					'timeout' => 4,
					'connect_timeout' => 4,
					'headers' => [
						'Referer' => $referer
					]
				]);

				$redirect = null;
				$content = '';
				$body = $response->getBody();
				$status = $response->getStatusCode();
				$is_html = false;

				if (!empty($response->getHeader('Refresh'))) {
					throw new UnsafeURLException('Redirection via Refresh header forbidden!');
				}

				if (in_array($status, [301, 302, 303, 307])) {
					$redirect = $response->getHeader('Location')[0] ?? null;
				}

				if ($redirect === null) {
					$content_type = $response->getHeader('Content-Type')[0] ?? null;
					$is_html = empty($content_type) || strpos($content_type, 'html') !== false;

					if ($is_html) {
						while (!$body->eof() && strlen($content) < 16384) {
							$content .= $body->read(2048);
						}

						$content = self::stripHTMLSpaces($content);
						$redirect = self::getClientRedirect($content);
					}
				}

				$body->close();

				if ($redirect === null) {
					return [
						'title' => $is_html ? self::getTitle($content) : null
					];
				}

				$redirect = UriResolver::resolve($url, new Uri($redirect));
				if ($redirect === $url) {
					throw new \RuntimeException('Redirect loop detected!');
				}

				$referer = $url;
				$url = $redirect;
			}

			throw new UnsafeURLException('Too many redirects');
		}
		catch (\RuntimeException $e) {
			# We couldn't get the contents of the URL; return a blank title.
			return [
				'title' => null
			];
		}
	}
}