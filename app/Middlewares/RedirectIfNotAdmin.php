<?php

namespace Elbo\Middlewares;

use Twig_Environment;
use Elbo\Models\User;
use Elbo\Library\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectIfNotAdmin {
	public $twig;
	public $session;

	public function __construct(Twig_Environment $twig, Session $session) {
		$this->twig = $twig;
		$this->session = $session;
	}

	public function handle(Request $request, $next) {
		$id = $this->session->get('userid');
		$user = User::where('id', $id)->select('admin', 'disabled', 'email')->first();

		if ($user === null) {
			return new RedirectResponse('/~login?redirect='.urlencode($request->getPathInfo()));
		}
		else if (!$user->admin || $user->disabled) {
			return new Response($this->twig->render('errors/notfound.html.twig', [
				'login_email' => $user->email ?? null
			]), 404);
		}

		return $next();
	}
}
