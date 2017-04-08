<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Library\Controller, RateLimiters\LoginRateLimiter};

class LoginPageController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedIn'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);
		$ratelimiter = $this->container->get(LoginRateLimiter::class);

		return new Response($twig->render('auth/login.html.twig', [
			'show_captcha' => !$ratelimiter->isAllowed($request->getClientIp())
		]));
	}
}
