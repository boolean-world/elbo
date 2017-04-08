<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\RedirectResponse;

trait RedirectIfLoggedIn {
	protected function redirectIfLoggedIn() {
		if ($this->session->get('userid') !== null) {
			return new RedirectResponse($this->redirectUrl ?? "/");
		}

		return $this->next();
	}
}
