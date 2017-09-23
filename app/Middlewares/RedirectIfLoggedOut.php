<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\{Request, RedirectResponse};

trait RedirectIfLoggedOut {
	protected function redirectIfLoggedOut(Request $request) {
		if ($this->session->get('userid') === null) {
			return new RedirectResponse($this->redirectUrl ?? '/~login?redirect='.urlencode($request->getPathInfo()));
		}

		return $this->next();
	}
}
