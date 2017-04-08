<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\ShortURL, Models\User, Models\Stats, Models\ShortenHistory, Library\Controller};

class ShortURLDeleteController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\NotFoundIfNotAdmin;
	use \Elbo\Middlewares\CSRFProtected;

	protected $middlewares = [
		'manageSession',
		'notFoundIfNotAdmin',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$context = [
			'r_shorturl' => $request->query->get('r_shorturl'),
			'r_title' => $request->query->get('r_title'),
			'r_url' => $request->query->get('r_url'),
			'r_custom' => $request->query->get('r_custom'),
			'r_disabled' => $request->query->get('r_disabled'),
			'r_ip' => $request->query->get('r_ip'),
			'r_n' => $request->query->get('r_n'),
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		];

		$entry = ShortURL::where('shorturl', $data['shorturl'])->first();

		if ($entry === null) {
			return new Response($twig->render('admin/link_notfound.html.twig', $context), 404);
		}

		$entry->delete();

		ShortenHistory::where('shorturl', $data['shorturl'])->delete();
		Stats::where('shorturl', $data['shorturl'])->delete();

		return new Response($twig->render('admin/link_deleted.html.twig', $context));
	}
}
