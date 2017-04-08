<?php

namespace Elbo\Library;

use GuzzleHttp\{Client, TransferStats};
use Elbo\Models\{ShortURL, DomainPolicy, ShortenHistory};
use Elbo\Exceptions\{URLShortenerException, InvalidURLException};

class URLShortener {
	private $client;

	public function isValidShortURL(string $shorturl) {
		return preg_match('/^[a-z0-9-]{1,70}$/i', $shorturl);
	}

	public function addToHistory(string $shorturl, string $userid) {
		ShortenHistory::create([
			'shorturl' => $shorturl,
			'userid' => $userid,
			'created_at' => time()
		]);
	}

	public function __construct() {
		$this->client = new Client([
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:50.0) Gecko/20100101 Firefox/50.0',
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Encoding' => 'gzip, deflate',
				'Accept-Language' => 'en-US,en;q=0.5'
			],
			'allow_redirects' => [
				'referer' => true
			],
			'cookies' => true
		]);
	}

	public function shorten(string $url, string $ip, string $shorturl = null, int $userid = null) {
		$url = new URL($url);
		$url_str = $url->getURL();
		$url_hostname = $url->getHostName();

		$custom = ($shorturl !== null && $shorturl !== '');

		if ($custom && !self::isValidShortURL($shorturl)) {
			throw new URLShortenerException('Invalid short URL.', URLShortenerException::SHORTURL_INVALID);
		}

		$visited_domains = [];

		if (!DomainPolicy::isAllowed($url_hostname)) {
			throw new URLShortenerException('Not allowed to shorten this URL', URLShortenerException::PROHIBITED_URL);
		}

		$visited_domains[$url_hostname] = true;

		if ($custom) {
			$res = ShortURL::where('shorturl', $shorturl)->first();

			if ($res !== null) {
				if ($res->url === $url_str) {
					if ($userid !== null) {
						$this->addToHistory($shorturl, $userid);
					}

					return [
						'shorturl' => $res->shorturl,
						'title' => $res->title ?? "",
						'url' => $res->url
					];
				}

				throw new URLShortenerException('Short URL already taken', URLShortenerException::SHORTURL_TAKEN);
			}
		}
		else {
			$res = ShortURL::where('url', $url_str)->where('custom', false)->first();

			if ($res !== null) {
				if ($userid !== null) {
					$this->addToHistory($res->shorturl, $userid);
				}

				return [
					'shorturl' => $res->shorturl,
					'title' => $res->title ?? "",
					'url' => $res->url
				];
			}
		}

		$title = null;

		try {
			$response = $this->client->request('GET', $url_str, [
				'stream' => true,
				'timeout' => 5,
				'connect_timeout' => 5,
				'on_stats' => function(TransferStats $stats) {
					$redir_hostname = $stats->getEffectiveUri()->getHost();

					if (!isset($visited_domains[$redir_hostname])) {
						if (!DomainPolicy::isAllowed($redir_hostname)) {
							throw new URLShortenerException('Not allowed to shorten this URL',
								URLShortenerException::PROHIBITED_URL);
						}
					}
				}
			]);

			$body = $response->getBody();
			$buf = '';
			$buf_len = 0;

			while (!$body->eof() && $buf_len <= 32767) {
				$tmp_buf = $body->read(1024);
				$buf_len += strlen($tmp_buf);
				$buf .= $tmp_buf;
			}

			$body->close();

			if (preg_match('/<\s*title[^>]*>([^<]+)/m', $buf, $matches)) {
				$title = html_entity_decode(trim($matches[1]), ENT_QUOTES);

				# Non UTF-8 encodings aren't handled so well, so...
				$title = (mb_check_encoding($title, "UTF-8")) ? mb_substr($title, 0, 100) : null;
			}
			else {
				$title = null;
			}

			# Check if there's a meta refresh or a page refresh with JS to another domain.
			# $matches[1] contains a meta-refresh URL.
			# $matches[2] contains a JS redirect URL.
			if (preg_match('~<\s*meta\s+[^>]*http-equiv\s*=\s*["\']?\s*refresh\s*["\']?\s+[^>]*content\s*=\s*["\']?\s*\d+\s*;\s*url\s*=\s*https?://([^"\'/\s]+)(:[0-9]+)?|<\s*script[^>]*>(?:.|\n)*window\.location\s*=\s*["\']https?://([^"\':/\s]+)(:[0-9]+)?~mi', $buf, $matches)) {
				$redir_hostname = $matches[1] ?? $matches[2];

				if ($redir_hostname !== null && !isset($visited_domains[$redir_hostname])) {
					if (!DomainPolicy::isAllowed($redir_hostname)) {
						throw new URLShortenerException('Not allowed to shorten this URL',
							URLShortenerException::PROHIBITED_URL);
					}
				}
			}
		}
		catch (\RuntimeException $re) {
			# We couldn't get the contents of the URL; nothing requires to be done.
			# Guzzle usually throws a GuzzleHttp\Exception\TransferException or one
			# of its derived exceptions, but in some rare cases a RuntimeException
			# can also be thrown.
		}

		if (!$custom) {
			for ($i = 0; $i < 35; $i++) {
				$shorturl = Random::string(3, $i <= 1 ? 4 : 8);

				if (ShortURL::where('shorturl', $shorturl)->count() === 0) {
					break;
				}
			}

			if ($i === 35) {
				throw new URLShortenerException('Failed to create short url',
					URLShortenerException::SHORTURL_CREATION_FAILED);
			}
		}

		$created_at = time();

		ShortURL::create([
			'shorturl' => $shorturl,
			'url' => $url_str,
			'ip' => $ip,
			'title' => $title,
			'custom' => $custom,
			'disabled' => false,
			'created_at' => $created_at,
			'userid' => $userid
		]);

		if ($userid !== null) {
			$this->addToHistory($shorturl, $userid);
		}

		return [
			'shorturl' => $shorturl,
			'title' => $title ?? "",
			'url' => $url_str
		];
	}
}
