<?php

namespace Elbo\Controllers;

use ReCaptcha\ReCaptcha;
use Elbo\{Models\Stats, Library\URLShortener, Library\Controller};
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Elbo\Exceptions\{InvalidURLException, URLShortenerException, UnsafeURLException};

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
		catch (UnsafeURLException $e) {
			return new JsonResponse([
				'status' => false,
				'reason' => 'prohibited_url'
			]);
		}
		catch (URLShortenerException $e) {
			$code = $e->getCode();

			if ($code === URLShortenerException::SHORTURL_INVALID) {
				$reason = 'shorturl_invalid';
			}
			else if ($code === URLShortenerException::SHORTURL_CREATION_FAILED) {
				$reason = 'internal_error';
			}
			else if ($code === URLShortenerException::SHORTURL_TAKEN) {
				$reason = 'shorturl_taken';
			}

			return new JsonResponse([
				'status' => false,
				'reason' => $reason
			]);
		}
	}
}
