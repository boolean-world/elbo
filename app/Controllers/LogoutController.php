<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\User, Models\RememberToken};
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

class LogoutController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedOut',
		'csrfProtected'
	];

	protected $redirectUrl = '/';

	public function run(Request $request, array &$data) {
		$this->session->destroy();

		$response = new RedirectResponse('/');
		$token = $request->cookies->get('remembertoken');

		if ($token !== null) {
			RememberToken::where('authenticator', hash('sha256', $token))->delete();
			$response->headers->clearCookie('remembertoken');
		}

		return $response;
	}
}
