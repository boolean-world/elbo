<?php

namespace Elbo\Middlewares;

use Elbo\Models\{User, RememberToken};
use Symfony\Component\HttpFoundation\Request;

trait PersistLogin {
	protected function persistLogin(Request $request) {
		if ($this->session->get('userid') !== null) {
			return $this->next();
		}

		$token = $request->cookies->get('remembertoken');

		if ($token === null) {
			return $this->next();
		}

		$tokeninfo = RememberToken::where('authenticator', hash('sha256', $token))->first();

		if ($tokeninfo === null) {
			// Introduce a random delay to penalize attackers.
			usleep(random_int(100000, 300000));

			$response = $this->next();
			$response->headers->clearCookie('remembertoken');

			return $response;
		}

		if (time() - $tokeninfo->created_at > 2592000) {
			// The token has expired, delete it.
			$tokeninfo->delete();

			$response = $this->next();
			$response->headers->clearCookie('remembertoken');

			return $response;
		}

		if (!$this->session->isStarted()) {
			$this->session->start();
		}
		else {
			$this->session->regenerate();
		}

		$this->session->set('userid', $tokeninfo->userid);

		User::where('id', $tokeninfo->userid)->update([
			'last_login' => time(),
			'last_login_ip' => $request->getClientIp()
		]);

		return $this->next();
	}
}
