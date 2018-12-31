<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Elbo\Library\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectIfLoggedIn {
	public $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}

	public function handle(Request $request, $next) {
		$id = $this->session->get('userid');

		if ($id !== null) {
			if (User::where('id', $id)->where('disabled', '<>', 1)->count() === 1) {
				return new RedirectResponse('/~home');
			}

			// Session carries bad data, destroy it.
			$this->session->destroy();
		}

		return $next();
	}
}