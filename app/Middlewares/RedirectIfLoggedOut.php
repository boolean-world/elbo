<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, RedirectResponse};

trait RedirectIfLoggedOut {
	protected function redirectIfLoggedOut(Request $request) {
		$id = $this->session->get('userid');

		if ($id !== null) {
			if (User::where('id', $id)->where('disabled', '<>', 1)->count() === 1) {
				return $this->next();
			}

			// Session carries bad data, destroy it.
			$this->session->destroy();
		}

		return new RedirectResponse($this->redirectUrl ?? '/~login?redirect='.urlencode($request->getPathInfo()));
	}
}
