<?php

namespace Elbo\Middlewares;

use Twig_Environment;
use Elbo\Models\User;
use Elbo\Library\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCSRFProtected {
	public $twig;
	public $session;

	public function __construct(Twig_Environment $twig, Session $session) {
		$this->twig = $twig;
		$this->session = $session;
	}

	public function handle(Request $request, $next) {
		if ($request->getMethod() === 'POST') {
			$referer = $request->headers->get('Origin') ?? $request->headers->get('Referer');
			$referer_domain = preg_replace('#^https?://([^/]+)#i', '\1', $referer);
			$host = $request->headers->get('Host');

			if ($referer_domain !== $host) {
				$userid = $this->session->get('userid');
				
				if ($userid === null) {
					$login_email = null;
				}
				else {
					$login_email = User::where('id', $userid)->pluck('email')->first();
				}

				return new Response($this->twig->render('errors/csrf.html.twig', [
					'login_email' => $login_email
				]), 403);
			}
		}

		return $next();
	}
}
