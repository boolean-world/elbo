<?php

namespace Elbo\Controllers;

use MaxMind\Db\Reader as GeoIP;
use Elbo\Models\{ShortURL, Stats};
use Elbo\Exceptions\InvalidURLException;
use Elbo\Library\{Utils, Controller, URL};
use Sinergi\BrowserDetector\{Browser, Os};
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};

class RedirectController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\ShortURLVerified;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'shortURLVerified'
	];

	public function run(Request $request, array &$data) {
		$url = ShortURL::where('shorturl', $data['shorturl'])->pluck('url')->first();

		$year = (int)date('Y');
		$month = (int)date('m');
		$date = (int)date('d');
		$user_agent = $request->headers->get('User-Agent');

		if (!preg_match('#bot|spider|crawl|https?://|preview|seo#i', $user_agent)) {
			$platform = (new Os($user_agent))->getName();
			$browser = (new Browser($user_agent))->getName();
		}
		else {
			$platform = null;
			$browser = null;
		}

		$geoip = $this->container->get(GeoIP::class);
		$country = $geoip->get($request->getClientIp())["registered_country"]["names"]["en"] ?? null;

		try {
			$referer = $request->headers->get('Origin') ?? $request->headers->get('Referer');
			$referer_domain = URL::determineHostName($referer);
		}
		catch (InvalidURLException $e) {
			$referer_domain = null;
		}

		$st = Stats::where('shorturl', $data['shorturl'])
		           ->where('year', $year)
		           ->where('month', $month)
		           ->where('date', $date)
		           ->where('referer', $referer_domain)
		           ->where('country', $country)
		           ->where('browser', $browser)
		           ->where('platform', $platform)
		           ->first();

		if ($st === null) {
			Stats::create([
				'year' => $year,
				'month' => $month,
				'date' => $date,
				'referer' => $referer_domain,
				'country' => $country,
				'browser' => $browser,
				'platform' => $platform,
				'count' => 1,
				'shorturl' => $data['shorturl']
			]);
		}
		else {
			$st->count++;
			$st->save();
		}

		return new RedirectResponse($url, 301);
	}
}
