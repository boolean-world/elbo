<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class Session {
	public $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}

	public function handle(Request $request, $next) {
		$before_sessionid = $request->cookies->get('sessionid');
		$this->session->bind($before_sessionid);

		$response = $next();
		$after_sessionid = $this->session->getId();

		// the session ID was modified.
		if ($after_sessionid !== $before_sessionid) { 
			// the session was expired.
			if ($after_sessionid === null && $before_sessionid !== null) {
				$response->headers->clearCookie('sessionid');
			}
			// the session was initiated/regenerated.
			else {
				$response->headers->setCookie(new Cookie('sessionid', $after_sessionid));
			}
		}

		if ($after_sessionid !== null) {
			$this->session->save();
		}

		return $response;
	}
}