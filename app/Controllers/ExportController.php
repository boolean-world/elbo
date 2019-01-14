<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\ShortenHistory};
use Symfony\Component\HttpFoundation\{Request, Response};

class ExportController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedOut'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');
		$res = ShortenHistory::distinct()
							 ->select('shorten_history.shorturl', 'short_url.title', 'short_url.url')
							 ->join('short_url', 'short_url.shorturl', '=', 'shorten_history.shorturl')
							 ->where('shorten_history.userid', $userid)
							 ->get();

		$outn = '/tmp/'.microtime(true);
		$out = fopen($outn, 'w');
		$content = '';

		fputcsv($out, ["shorturl", "url", "title"]);

		foreach ($res as $r) {
			fputcsv($out, [$r->shorturl, $r->url, $r->title]);
		}

		fclose($out);
		$content = file_get_contents($outn);

		unlink($outn);

		return new Response($content, 200, [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'filename="short-links.csv"'
		]);
	}
}
