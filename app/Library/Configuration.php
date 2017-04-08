<?php

namespace Elbo\Library;

use Symfony\Component\Yaml\Yaml;

class Configuration {
	private $config;

	public function __construct() {
		$cachefile = __DIR__.'/../../data/cache/config';

		if (file_exists($cachefile)) {
			$res = require $cachefile;

			if (!is_array($res)) {
				throw new \RuntimeException('Invalid configuration cache file');
			}

			$this->config = $res;
		}
		else {
			$this->config = Yaml::parse(file_get_contents(__DIR__.'/../../data/config/elbo.yml'));

			if (($this->config['environment']['phase'] ?? null) === 'production') {
				file_put_contents($cachefile, '<?php return '.var_export($this->config, true).';');
			}
		}
	}

	public function get($key) {
		$throw_exception = (func_num_args() < 2);
		$ref = &$this->config;

		foreach (explode('.', $key) as $i) {
			if(!isset($ref[$i])) {
				if ($throw_exception) {
					throw new \RuntimeException("Configuration $key does not exist.");
				}

				return func_get_arg(1);
			}

			$ref = &$ref[$i];
		}

		return $ref;
	}
}
