<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, RedirectResponse};
use Elbo\{Library\Controller, Models\User, Models\ShortenHistory, Models\RememberToken};

class AccountDeleteController extends Controller {
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

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');

		$user = User::where('id', $userid)->update([
			'disabled' => true
		]);

		ShortenHistory::where('userid', $userid)->delete();
		RememberToken::where('userid', $userid)->delete();

		$this->session->destroy();

		$response = new RedirectResponse('/');
		$response->headers->clearCookie('remembertoken');

		return $response;
	}
}
