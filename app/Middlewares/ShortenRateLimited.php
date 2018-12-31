<?php

namespace Elbo\Middlewares;

use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elbo\RateLimiters\AnonShortenRateLimiter as AnonLimiter;
use Elbo\RateLimiters\UserShortenRateLimiter as UserLimiter;

class ShortenRateLimited {
	public $session;
	public $ulimiter;
	public $alimiter;
	public $recaptcha;

	public function __construct(Session $session, ReCaptcha $recaptcha, UserLimiter $ulimiter, AnonLimiter $alimiter) {
		$this->session = $session;
		$this->ulimiter = $ulimiter;
		$this->alimiter = $alimiter;
		$this->recaptcha = $recaptcha;
	}

	public function handle(Request $request, $next) {
		$userid = $this->session->get('userid');
		$ip = $request->getClientIp();

		if ($userid !== null) {
			$ratelimiter = $this->ulimiter;
			$identifier = $userid;
		}
		else {
			$ratelimiter = $this->alimiter;
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

		return $next();
	}
}