<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\User};
use Symfony\Component\HttpFoundation\{Request, Response};

class NotFoundController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;

	protected $middlewares = [
		'manageSession',
		'persistLogin'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');
		
		if ($userid === null) {
			$login_email = null;
		}
		else {
			$login_email = User::where('id', $userid)->pluck('email')->first();
		}

		$twig = $this->container->get(\Twig_Environment::class);

		return new Response($twig->render('errors/notfound.html.twig', [
			'login_email' => $login_email
		]), 404);
	}
}
