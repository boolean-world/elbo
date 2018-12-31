<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, RedirectResponse};

class RedirectIfLoggedOut {
	public $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}

	public function handle(Request $request, $next) {
		$id = $this->session->get('userid');

		if ($id !== null) {
			if (User::where('id', $id)->where('disabled', '<>', 1)->count() === 1) {
				return $next();
			}

			// Session carries bad data, destroy it.
			$this->session->destroy();
		}

		return new RedirectResponse($this->redirectUrl ?? '/~login?redirect='.urlencode($request->getPathInfo()));
	}
}