<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\User};
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

class LogoutController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedOut',
		'csrfProtected'
	];

	protected $redirectUrl = '/';

	public function run(Request $request, array &$data) {
		$this->session->destroy();

		return new RedirectResponse("/");
	}
}
