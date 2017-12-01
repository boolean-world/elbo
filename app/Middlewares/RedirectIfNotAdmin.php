<?php

namespace Elbo\Middlewares;

use Elbo\Models\User;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

trait RedirectIfNotAdmin {
	protected function redirectIfNotAdmin(Request $request) {
		$id = $this->session->get('userid');
		$user = User::where('id', $id)->where('disabled', '<>', 1)->select('admin')->first();

		if ($user === null) {
			return new RedirectResponse('/~login?redirect='.urlencode($request->getPathInfo()));
		}
		else if ($user->admin !== 1) {
			$twig = $this->container->get(\Twig_Environment::class);

			return new Response($twig->render('errors/notfound.html.twig', [
				'login_email' => $user->email ?? null
			]), 404);
		}

		return $this->next();
	}
}
