<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, JsonResponse};
use Elbo\{Library\Controller, RateLimiters\UserShortenRateLimiter, RateLimiters\AnonShortenRateLimiter};

class RateLimitController extends Controller {
	use \Elbo\Middlewares\Session;

	protected $middlewares = [
		'manageSession'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');

		if ($userid !== null) {
			$ratelimiter = $this->container->get(UserShortenRateLimiter::class);
			$identifier = $userid;
		}
		else {
			$ratelimiter = $this->container->get(AnonShortenRateLimiter::class);
			$identifier = $request->getClientIp();
		}

		return new JsonResponse([
			'status' => $ratelimiter->isAllowed($identifier)
		]);
	}
}
