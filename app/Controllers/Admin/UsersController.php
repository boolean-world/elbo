<?php

namespace Elbo\Controllers\Admin;

use Elbo\{Models\User, Library\Controller};
use Symfony\Component\HttpFoundation\{Request, Response};

class UsersController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\NotFoundIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'notFoundIfNotAdmin'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$pagenum = (int)($request->query->get('n'));
		$skip = 30 * $pagenum;
		$email = trim($request->query->get('email'));
		$last_login_ip = trim($request->query->get('last_login_ip'));
		$created_ip = trim($request->query->get('created_ip'));
		$disabled = (int)($request->query->get('disabled'));

		$query = User::skip($skip)->take(31);

		if ($email !== '') {
			$query->where('email', 'like', $email);
		}

		if ($last_login_ip !== '') {
			$query->where('last_login_ip', 'like', $last_login_ip);
		}

		if ($created_ip !== '') {
			$query->where('created_from', 'like', $created_ip);
		}

		if ($disabled === 1 || $disabled === 2) {
			$query->where('disabled', $disabled === 1);
		}

		$entries = $query->get();

		if ($entries->count() > 30) {
			$has_more = true;
		}
		else {
			$has_more = false;
		}

		return new Response($twig->render('admin/users.html.twig', [
			'entries' => $entries->slice(0, 30),
			'has_more' => $has_more,
			'form' => compact('email', 'last_login_ip', 'created_ip', 'disabled'),
			'pagenum' => $pagenum,
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
