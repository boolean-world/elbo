<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\RedirectResponse;

trait RedirectIfLoggedOut {
	protected function redirectIfLoggedOut() {
		if ($this->session->get('userid') === null) {
			return new RedirectResponse($this->redirectUrl ?? '/~login');
		}

		return $this->next();
	}
}
