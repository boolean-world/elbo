<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, Response};

trait NotFoundIfNotAdmin {
	protected function notFoundIfNotAdmin(Request $request) {
		$userid = $this->session->get('userid');
		$user = User::where('id', $userid)->first();

		if ($user === null || $user->admin !== 1) {
			$twig = $this->container->get(\Twig_Environment::class);

			return new Response($twig->render('errors/notfound.html.twig', [
				'login_email' => $user->email ?? null
			]), 404);
		}

		return $this->next();
	}
}
