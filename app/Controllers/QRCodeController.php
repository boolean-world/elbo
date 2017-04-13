<?php

namespace Elbo\Controllers;

use Elbo\Models\{ShortURL, User};
use Elbo\Library\{Utils, Controller, URL};
use Symfony\Component\HttpFoundation\{Request, Response};

class QRCodeController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\ShortURLVerified;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'shortURLVerified'
	];

	protected $warningPage = 'qr_disabled.html.twig';

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		return new Response($twig->render('qr.html.twig', [
			'shorturl' => $data['shorturl'],
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
