<?php

namespace Elbo\Controllers;

use Symfony\Component\HttpFoundation\{Request, RedirectResponse};
use Elbo\{Library\Controller, Models\User, Models\ShortenHistory};

class AccountDeleteController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedOut',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');

		$user = User::where('id', $userid)->update([
			'disabled' => true
		]);

		ShortenHistory::where('userid', $userid)->delete();

		$this->session->destroy();
		return new RedirectResponse('/');
	}
}
