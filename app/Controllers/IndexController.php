<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\User};
use Symfony\Component\HttpFoundation\{Request, Response};

class IndexController extends Controller {
	use \Elbo\Middlewares\Session;

	protected $middlewares = [
		'manageSession'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');
		$twig = $this->container->get(\Twig_Environment::class);

		if ($userid === null) {
			return new Response($twig->render('welcome.html.twig'));
		}

		$login_email = User::where('id', $userid)->pluck('email')->first();

		return new Response($twig->render('home.html.twig', [
			'login_email' => $login_email,
			'time' => time()
		]));
	}
}
