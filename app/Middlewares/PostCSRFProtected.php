<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, Response};

trait PostCSRFProtected {
	protected function postCSRFProtected(Request $request) {
		if ($request->getMethod() === 'POST') {
			$referer = $request->headers->get('Origin') ?? $request->headers->get('Referer');
			$referer_domain = preg_replace('#^https?://([^/]+)(?:/.*)?$#i', '\1', $referer);
			$host = $request->headers->get('Host');

			if ($referer_domain !== $host) {
				$userid = $this->session->get('userid');
				
				if ($userid === null) {
					$login_email = null;
				}
				else {
					$login_email = User::where('id', $userid)->pluck('email')->first();
				}

				$twig = $this->container->get(\Twig_Environment::class);

				return new Response($twig->render('errors/csrf.html.twig', [
					'login_email' => $login_email
				]), 403);
			}
		}

		return $this->next();
	}
}
