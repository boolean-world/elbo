<?php

namespace Elbo\Controllers;

use Elbo\Library\{Utils, Controller};
use Elbo\Models\{ShortURL, Stats, User};
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

class AnalyticsDataController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfLoggedOut;
	use \Elbo\Middlewares\ShortURLVerified;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedOut',
		'shortURLVerified'
	];

	protected $warningPage = 'stats_unavailable.html.twig';

	public function run(Request $request, array &$data) {
		$shorturl = $data['shorturl'];
		$now = time();

		$referer_query = Stats::where('shorturl', $data['shorturl'])
		                       ->groupBy('referer')
		                       ->select(DB::raw('sum(count) as count, referer'));

		$browser_query = Stats::where('shorturl', $data['shorturl'])
		                       ->groupBy('browser')
		                       ->select(DB::raw('sum(count) as count, browser'));

		$country_query = Stats::where('shorturl', $data['shorturl'])
		                       ->groupBy('country')
		                       ->select(DB::raw('sum(count) as count, country'));

		$platform_query = Stats::where('shorturl', $data['shorturl'])
		                       ->groupBy('platform')
		                       ->select(DB::raw('sum(count) as count, platform'));

		$click_query = Stats::where('shorturl', $data['shorturl']);

		$click_stats = [];

		if (!isset($data['duration']) || $data['duration'] === 'week') {
			for ($i = 0; $i < 7; $i++) {
				$date[$i] = (int)date('d', $now - $i * 86400);
				$month[$i] = (int)date('m', $now - $i * 86400);
				$year[$i] = (int)date('Y', $now - $i * 86400);

				$click_stats += [
					date("d M Y", $now - $i * 86400) => Stats::where('shorturl', $data['shorturl'])
					                                         ->where('date', $date[$i])
					                                         ->where('month', $month[$i])
					                                         ->where('year', $year[$i])
					                                         ->sum('count')
				];
			}

			$referer_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 7; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$browser_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 7; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$country_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 7; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$platform_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 7; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});
		}
		else if ($data['duration'] === 'month') {
			for ($i = 0; $i < 30; $i++) {
				$date[$i] = (int)date('d', $now - $i * 86400);
				$month[$i] = (int)date('m', $now - $i * 86400);
				$year[$i] = (int)date('Y', $now - $i * 86400);

				$click_stats += [
					date("d M Y", $now - $i * 86400) => Stats::where('shorturl', $data['shorturl'])
					                                         ->where('date', $date[$i])
					                                         ->where('month', $month[$i])
					                                         ->where('year', $year[$i])
					                                         ->sum('count')
				];
			}

			$referer_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 30; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$browser_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 30; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$country_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 30; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$platform_query->where(function($query) use ($date, $month, $year) {
				for ($i = 0; $i < 30; $i++) {
					$query->orWhere([
						'date' => $date[$i],
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});
		}
		else if ($data['duration'] === 'year') {
			for ($i = 0; $i < 12; $i++) {
				$month[$i] = (int)date('m', strtotime("-${i} month", $now));
				$year[$i] = (int)date('Y', strtotime("-${i} month", $now));

				$click_stats += [
					date("M Y",  strtotime("-${i} month", $now)) => Stats::where('shorturl', $data['shorturl'])
					                                                     ->where('month', $month[$i])
					                                                     ->where('year', $year[$i])
					                                                     ->sum('count')
				];
			}

			$referer_query->where(function($query) use ($month, $year) {
				for ($i = 0; $i < 12; $i++) {
					$query->orWhere([
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$browser_query->where(function($query) use ($month, $year) {
				for ($i = 0; $i < 12; $i++) {
					$query->orWhere([
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$country_query->where(function($query) use ($month, $year) {
				for ($i = 0; $i < 12; $i++) {
					$query->orWhere([
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});

			$platform_query->where(function($query) use ($month, $year) {
				for ($i = 0; $i < 12; $i++) {
					$query->orWhere([
						'month' => $month[$i],
						'year' => $year[$i]
					]);
				}
			});
		}

		$referer_stats = $referer_query->get();
		$browser_stats = $browser_query->get();
		$country_stats = $country_query->get();
		$platform_stats = $platform_query->get();

		return new JsonResponse(compact('click_stats','referer_stats', 'browser_stats', 'country_stats', 'platform_stats') + [
			'status' => true
		]);
	}
}
