<?php

namespace Elbo\Library;

use Elbo\Library\Configuration;

abstract class RateLimiter {
	protected $redis;
	protected $requests;
	protected $timeframe;
	protected $redis_key_prefix;

	public function __construct(Configuration $config, \Redis $redis) {
		$this->redis = $redis;
		$prefix = $this->getPrefix(get_called_class());
		$this->redis_key_prefix = "elbo:rl:$prefix:";
		$this->requests = $config->get("ratelimiter.$prefix.requests", 5);
		$this->timeframe = $config->get("ratelimiter.$prefix.timeframe", 1);
	}

	private function getPrefix(string $classname) {
		$basename = class_basename($classname);
		$pos = strrpos($basename, 'RateLimiter');

		if ($pos !== false && $pos !== 0) {
			$basename = substr($basename, 0, $pos);
		}

		$len = strlen($basename);
		$i = 0;
		$rv = '';

		while ($i < $len) {
			if ($i !== $len - 1 && ctype_lower($basename[$i]) && ctype_upper($basename[$i + 1])) {
				$rv .= $basename[$i].'_'.strtolower($basename[$i + 1]);
				$i++;
			}
			else {
				$rv .= strtolower($basename[$i]);
			}

			$i++;
		}

		return $rv;
	}

	protected function getKey(string $identifier) {
		return $this->redis_key_prefix.$identifier;
	}

	public function increment(string $identifier) {
		$key = $this->getKey($identifier);
		$this->redis->incr($key);

		if ($this->redis->ttl($key) === -1) {
			$this->redis->setTimeout($key, $this->timeframe * 60);
		}
	}

	public function isAllowed(string $identifier) {
		$key = $this->getKey($identifier);
		return ($this->redis->get($key) < $this->requests);
	}

	public function reset(string $identifier) {
		$key = $this->getKey($identifier);
		$this->redis->del($key);
	}
}
