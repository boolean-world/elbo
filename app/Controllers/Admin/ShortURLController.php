<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\User, Models\ShortURL, Library\Controller};

class ShortURLController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\NotFoundIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'notFoundIfNotAdmin'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$pagenum = (int)($request->query->get('n'));
		$skip = 30 * $pagenum;
		$shorturl = trim($request->query->get('shorturl'));
		$title = trim($request->query->get('title'));
		$url = trim($request->query->get('url'));
		$custom = (int)($request->query->get('custom'));
		$disabled = (int)($request->query->get('disabled'));
		$ip = trim($request->query->get('ip'));

		$query = ShortURL::select('short_url.shorturl','short_url.url', 'short_url.custom',
		                          'short_url.created_at', 'short_url.ip', 'short_url.title',
		                          'short_url.disabled', 'users.email')
		                 ->leftJoin('users', 'short_url.userid', '=', 'users.id')
		                 ->orderBy('created_at', 'desc')
		                 ->skip($skip)
		                 ->take(31);

		if ($shorturl !== '') {
			$query->where('shorturl', 'like', $shorturl);
		}

		if ($title !== '') {
			$query->where('title', 'like', $title);
		}

		if ($url !== '') {
			$query->where('url', 'like', $url);
		}

		if ($ip !== '') {
			$query->where('ip', 'like', $ip);
		}

		if ($custom === 1 || $custom === 2) {
			$query->where('custom', $custom === 1);
		}

		if ($disabled === 1 || $disabled === 2) {
			$query->where('short_url.disabled', $disabled === 1);
		}

		$entries = $query->get();

		if ($entries->count() > 30) {
			$has_more = true;
		}
		else {
			$has_more = false;
		}

		return new Response($twig->render('admin/shorturls.html.twig', [
			'entries' => $entries->slice(0, 30),
			'has_more' => $has_more,
			'form' => compact('shorturl', 'title', 'url', 'custom', 'disabled', 'ip'),
			'pagenum' => $pagenum,
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
