<?php

namespace Elbo\Controllers;

use Elbo\Library\{Utils, Controller};
use Elbo\Models\{ShortURL, Stats, User};
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\{Request, Response};

class AnalyticsController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\RedirectIfLoggedOut;
	use \Elbo\Middlewares\ShortURLVerified;

	protected $middlewares = [
		'manageSession',
		'redirectIfLoggedOut',
		'shortURLVerified'
	];

	protected $warningPage = 'stats_unavailable.html.twig';

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$count = Stats::where('shorturl', $data['shorturl'])->sum('count');
		$urlinfo = ShortURL::where('shorturl', $data['shorturl'])->select('url', 'created_at')->first();
		$login_email = User::where('id', $this->session->get('userid'))->pluck('email')->first();

		return new Response($twig->render('stats.html.twig', [
			'count' => $count,
			'urlinfo' => $urlinfo,
			'count' => $count,
			'shorturl' => $data['shorturl'],
			'login_email' => $login_email
		]));
	}
}
