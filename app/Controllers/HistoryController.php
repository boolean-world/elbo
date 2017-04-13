<?php

namespace Elbo\Controllers;

use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};
use Elbo\{Models\ShortenHistory, Models\Stats, Library\Controller};

class HistoryController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;

	protected $middlewares = [
		'manageSession',
		'persistLogin'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');

		if ($userid === null) {
			return new JsonResponse([
                'status' => false
            ], 400);
		}

		$skip = (int)($request->query->get('s'));
		$time = (int)($request->query->get('t'));

		if ($skip % 15 !== 0) {
			return new JsonResponse([
				'status' => false,
			], 400);
		}

		$hist = ShortenHistory::select('shorten_history.shorturl', 'short_url.title', 'short_url.url')
		                      ->join('short_url', 'short_url.shorturl', '=', 'shorten_history.shorturl')
		                      ->where('shorten_history.userid', '=', $userid)
		                      ->where('shorten_history.created_at', '<', $time)
		                      ->orderBy('shorten_history.created_at', 'desc')
		                      ->offset($skip)
		                      ->limit(15)
		                      ->get()
		                      ->toArray();

		$stats_query = Stats::select(DB::raw('shorturl, sum(count) as count'))
		                    ->groupBy('shorturl');

		foreach ($hist as $entry) {
			$stats_query->orWhere('shorturl', $entry['shorturl']);
		}

		$stats = $stats_query->get()->toArray();
		$i = 0;
		$c = count($hist);

		for ($i = 0; $i < $c; $i++) {
			for ($j = 0; $j < $c; $j++) {
				if (isset($stats[$j]) && $hist[$i]['shorturl'] === $stats[$j]['shorturl']) {
					$hist[$i]['clicks'] = $stats[$j]['count'] ;
				}
			}
		}

		return new JsonResponse([
			'status' => true,
			'result' => $hist
		]);
	}
}
