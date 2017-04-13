<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\PasswordReset};
use Symfony\Component\HttpFoundation\{Request, Response};

class PasswordResetTokenController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedIn'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);
		$token_info = PasswordReset::where('token', $data['token'])->first();

		if ($token_info === null || $token_info->expires_at < time()) {
			return new Response($twig->render('errors/notfound.html.twig'), 404);
		}

		return new Response($twig->render('auth/reset_pass.html.twig', [
			'token' => $data['token']
		]));
	}
}
