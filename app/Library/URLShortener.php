<?php

namespace Elbo\Library;

use GuzzleHttp\{Client, TransferStats};
use Elbo\Models\{ShortURL, ShortenHistory};
use Elbo\Exceptions\{URLShortenerException, InvalidURLException};

class URLShortener {
	protected $info;

	public static function isValidShortURL(string $shorturl) {
		return preg_match('/^[a-z0-9-]{1,70}$/i', $shorturl);
	}

	protected function addToHistory(string $shorturl, string $userid) {
		ShortenHistory::create([
			'shorturl' => $shorturl,
			'userid' => $userid,
			'created_at' => time()
		]);
	}

	public function __construct(URLInfoCollector $info) {
		$this->info = $info;
	}

	public function shorten(string $url, string $ip, string $shorturl = null, int $userid = null) {
		$url = new URL($url);
		$url_str = $url->getURL();
		$custom = ($shorturl !== null && $shorturl !== '');

		if ($custom && !self::isValidShortURL($shorturl)) {
			throw new URLShortenerException('Invalid short URL.', URLShortenerException::SHORTURL_INVALID);
		}

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

		$title = $this->info->getInfo($url)['title'];

		if (!$custom) {
			for ($i = 0; $i < 35; $i++) {
				$shorturl = Random::string(3, $i <= 1 ? 4 : 8);

				if (ShortURL::where('shorturl', $shorturl)->count() === 0) {
					break;
				}
			}

			if ($i === 35) {
				throw new URLShortenerException('Failed to create short url', URLShortenerException::SHORTURL_CREATION_FAILED);
			}
		}

		ShortURL::create([
			'shorturl' => $shorturl,
			'url' => $url_str,
			'ip' => $ip,
			'title' => $title,
			'custom' => $custom,
			'disabled' => false,
			'created_at' => time(),
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
