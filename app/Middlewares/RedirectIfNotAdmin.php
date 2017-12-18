<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

trait RedirectIfNotAdmin {
	protected function redirectIfNotAdmin(Request $request) {
		$id = $this->session->get('userid');
		$user = User::where('id', $id)->select('admin', 'disabled', 'email')->first();

		if ($user === null) {
			return new RedirectResponse('/~login?redirect='.urlencode($request->getPathInfo()));
		}
		else if (!$user->admin || $user->disabled) {
			$twig = $this->container->get(\Twig_Environment::class);

			return new Response($twig->render('errors/notfound.html.twig', [
				'login_email' => $user->email ?? null
			]), 404);
		}

		return $this->next();
	}
}
