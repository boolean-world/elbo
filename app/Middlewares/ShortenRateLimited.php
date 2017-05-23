<?php

namespace Elbo\Middlewares;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};
use Elbo\RateLimiters\{AnonShortenRateLimiter, UserShortenRateLimiter};

trait ShortenRateLimited {
	protected function shortenRateLimited(Request $request) {
		$userid = $this->session->get('userid');
		$ip = $request->getClientIp();

		if ($userid !== null) {
			$ratelimiter = $this->container->get(UserShortenRateLimiter::class);
			$identifier = $userid;
		}
		else {
			$ratelimiter = $this->container->get(AnonShortenRateLimiter::class);
			$identifier = $ip;
		}

		if (!$ratelimiter->isAllowed($identifier)) {
			$recaptcha = $this->container->get(ReCaptcha::class);
			$solved = $recaptcha->verify($request->request->get('recaptcha_response'), $ip);

			if (!$solved->isSuccess()) {
				return new JsonResponse([
					'status' => false,
					'reason' => 'ratelimited'
				]);
			}
		}

		$ratelimiter->increment($identifier);

		return $this->next();
	}
}
