<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Library\Controller, Models\User, Library\EmailValidator};

class AccountController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\PostCSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedOut',
		'postCSRFProtected'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');
		$login_email = User::where('id', $userid)->pluck('email')->first();

		$twig = $this->container->get(\Twig_Environment::class);
		$success = false;
		$errors = [];
		$action = $request->request->get('action');

		if ($action === 'email') {
			$email = $request->request->get('email');

			try {
				$emailvalidator = $this->container->get(EmailValidator::class);

				$normalized_email = $emailvalidator->normalize($email);

				if (!$emailvalidator->isAllowed($email)) {
					$errors['email'] = 1;
				}
				else if (User::where('normalized_email', $normalized_email)->where('id', '<>', $userid)->count() !== 0) {
					$errors['email'] = 2;
				}
			}
			catch (\InvalidArgumentException $e) {
				$errors['email'] = 1;
			}

			$twig = $this->container->get(\Twig_Environment::class);

			if (!$errors) {
				$user = User::where('id', $userid)->update([
					'email' => $email,
					'normalized_email' => $normalized_email
				]);

				$success = true;
				$login_email = $email;
			}
		}
		else if ($action === 'password') {
			$old_password = User::where('id', $userid)->pluck('password')->first();
			$old_password_input = $request->request->get('old_password');

			if (!password_verify($old_password_input, $old_password)) {
				$errors['password'] = 1;
			}
			else {
				$new_password = $request->request->get('new_password');
				$new_password_confirm = $request->request->get('new_password_confirm');

				if (strlen($new_password) < 6) {
					$errors['password'] = 2;
				}
				else if ($new_password === $old_password_input) {
					$errors['password'] = 4;
				}
				else if ($new_password !== $new_password_confirm) {
					$errors['password'] = 3;
				}
				else {
					User::where('id', $userid)->update([
						'password' => password_hash($new_password, PASSWORD_DEFAULT)
					]);

					$success = true;
				}
			}
		}

		return new Response($twig->render('account.html.twig', compact('errors', 'login_email', 'success')));
	}
}
