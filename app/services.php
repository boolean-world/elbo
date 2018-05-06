<?php

return (function() {
	$containerBuilder = new DI\ContainerBuilder();

	$config = new Elbo\Library\Configuration();

	if ($config->get('environment.phase') === 'production') {
		$containerBuilder->enableCompilation(__DIR__.'/../data/cache');
	}

	$containerBuilder->addDefinitions([
		Elbo\Library\Configuration::class => $config,

		Redis::class => function(Elbo\Library\Configuration $config) {
			$redis = new Redis();
			$socket = $config->get('redis.socket', null);

			if ($socket !== null) {
				$redis->connect($socket);
			}
			else {
				$redis->connect($config->get('redis.host'), $config->get('redis.port'));
			}

			return $redis;
		},

		BaconQrCode\Renderer\RendererInterface::class => DI\create(BaconQrCode\Renderer\Image\Png::class),

		ReCaptcha\ReCaptcha::class => function(Elbo\Library\Configuration $config) {
			return new ReCaptcha\ReCaptcha($config->get('api_key.recaptcha_secret_key'));
		},

		MaxMind\Db\Reader::class => function() {
			return new MaxMind\Db\Reader(__DIR__.'/../data/GeoLite2-Country.mmdb');
		},

		Nette\Mail\IMailer::class => function(Elbo\Library\Configuration $config) {
			if ($config->get('environment.phase') !== 'production') {
				return new Elbo\Library\MockMailer();
			}

			return new Nette\Mail\SmtpMailer($config->get('mailer.smtp'));
		},

		Twig_Environment::class => function(Elbo\Library\Configuration $config) {
			$fs_loader = new Twig_Loader_Filesystem(__DIR__.'/../resources/views/');
			$twig = new Twig_Environment($fs_loader);

			if ($config->get('environment.phase') === 'production') {
				$twig->setCache(__DIR__.'/../data/cache/twig/');
			}

			$twig->addFunction(new Twig_SimpleFunction('dump', function($var) {
				dump($var);
			}));

			$twig->addFunction(new Twig_SimpleFunction('config', function($key) use ($config) {
				return $config->get($key);
			}));

			$twig->addFunction(new Twig_SimpleFunction('current_year', function() {
				return date('Y');
			}));

			$twig->addFunction(new Twig_SimpleFunction('url', function($path, $arr) {
				$rv = $path;
				$begin_query_str = false;

				foreach ($arr as $param => $value) {
					if (!$begin_query_str) {
						$rv .= '?';
						$begin_query_str = true;
					}
					else {
						$rv .= '&';
					}

					$rv .= rawurlencode($param).'='.urlencode($value);
				}

				return $rv;
			}));

			return $twig;
		}
	]);

	return $containerBuilder->build();
})();
