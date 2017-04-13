<?php

namespace Elbo\Controllers;

use ReCaptcha\ReCaptcha;
use Elbo\Exceptions\{InvalidURLException, URLShortenerException};
use Elbo\{Models\Stats, Library\URLShortener, Library\Controller};
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};

class ShortenController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\ShortenRateLimited;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'shortenRateLimited'
	];

	public function run(Request $request, array &$data) {
		try {
			$url = trim($request->request->get('url'));
			$shorturl = trim($request->request->get('shorturl'));
			$ip = $request->getClientIp();

			$urlshortener = $this->container->get(URLShortener::class);
			$res = $urlshortener->shorten($url, $ip, $shorturl, $this->session->get('userid'));

			return new JsonResponse([
				'status' => true,
				'title' => $res['title'],
				'shorturl' => $res['shorturl'],
				'url' => $res['url'],
				'clicks' => Stats::where('shorturl', $res['shorturl'])->sum('count')
			]);
		}
		catch (InvalidURLException $e) {
			return new JsonResponse([
				'status' => false,
				'reason' => 'invalid_url'
			]);
		}
		catch (URLShortenerException $e) {
			switch ($e->getCode()) {
				case URLShortenerException::SHORTURL_INVALID:
					return new JsonResponse([
						'status' => false,
						'reason' => 'shorturl_invalid'
					]);
				case URLShortenerException::SHORTURL_CREATION_FAILED:
					return new JsonResponse([
						'status' => false,
						'reason' => 'internal_error'
					]);
				case URLShortenerException::SHORTURL_TAKEN:
					return new JsonResponse([
						'status' => false,
						'reason' => 'shorturl_taken'
					]);
				case URLShortenerException::PROHIBITED_URL:
					return new JsonResponse([
						'status' => false,
						'reason' => 'invalid_url'
					]);
			}
		}
	}
}
