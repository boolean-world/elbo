<?php

namespace Elbo\Controllers;

use ReCaptcha\ReCaptcha;
use Elbo\{Library\Controller, Models\User, RateLimiters\LoginRateLimiter};
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

class LoginHandlerController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedIn',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$ratelimiter = $this->container->get(LoginRateLimiter::class);
		$twig = $this->container->get(\Twig_Environment::class);

		$ip = $request->getClientIp();
		$email = $request->request->get('email');

		if (!$ratelimiter->isAllowed($ip)) {
			$recaptcha = $this->container->get(ReCaptcha::class);
			$grecaptcha_resp = $request->request->get('g-recaptcha-response');

			if (!$recaptcha->verify($grecaptcha_resp, $ip)->isSuccess()) {
				$ratelimiter->increment($ip);

				return new Response($twig->render('auth/login.html.twig', [
					'email' => $email,
					'errors' => [
						'captcha' => true
					],
					'show_captcha' => !$ratelimiter->isAllowed($ip)
				]), 403);
			}
		}

		$password = $request->request->get('password');
		$user = User::where('email', $email)->first();

		if ($user === null || $user->disabled || !password_verify($password, $user->password)) {
			$ratelimiter->increment($ip);

			return new Response($twig->render('auth/login.html.twig', [
				'email' => $email,
				'errors' => [
					'login' => true
				],
				'show_captcha' => !$ratelimiter->isAllowed($ip)
			]), 403);
		}

		if (!$this->session->isStarted()) {
			$this->session->start();
		}
		else {
			$this->session->regenerate();
		}

		$this->session->set('userid', $user->id);
		$user->last_login = time();
		$user->last_login_ip = $request->getClientIp();
		$user->save();

		return new RedirectResponse('/');
	}
}
