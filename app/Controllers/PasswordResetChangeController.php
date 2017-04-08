<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Library\Controller, Models\User, Models\PasswordReset};

class PasswordResetChangeController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedIn',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);
		$token_info = PasswordReset::where('token', $data['token'])->first();

		// allow for a "grace period" of 10 minutes to prevent edge cases
		// where the user enters a new password but the token expires at the
		// same time.
		if ($token_info === null || $token_info->expires_at + 600 < time()) {
			return new Response($twig->render('errors/notfound.html.twig'), 404);
		}

		$password = $request->request->get('password');
		$password_confirm = $request->request->get('password_confirm');
		$errors = [];

		if (strlen($password) < 6) {
			$errors['password'] = true;
		}
		else if ($password_confirm !== $password) {
			$errors['password_confirm'] = true;
		}

		if ($errors) {
			return new Response($twig->render('auth/reset_pass.html.twig', [
				'token' => $data['token'],
				'errors' => $errors
			]));
		}

		User::where('id', $token_info->userid)->update([
			'password' => password_hash($password, PASSWORD_DEFAULT)
		]);

		$token_info->delete();

		return new Response($twig->render('auth/reset_done.html.twig'));
	}
}
