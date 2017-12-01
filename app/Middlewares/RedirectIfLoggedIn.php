<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait RedirectIfLoggedIn {
	protected function redirectIfLoggedIn() {
		$id = $this->session->get('userid');

		if ($id !== null) {
			if (User::where('id', $id)->where('disabled', '<>', 1)->count() === 1) {
				return new RedirectResponse($this->redirectUrl ?? '/~home');
			}

			// Session carries bad data, destroy it.
			$this->session->destroy();
		}

		return $this->next();
	}
}
