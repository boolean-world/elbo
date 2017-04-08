<?php

namespace Elbo\Library;

abstract class RateLimiter {
	protected $redis;
	protected $requests = 5;
	protected $timeframe = 1;
	protected $prefix;

	public function __construct(\Redis $redis) {
		$this->redis = $redis;

		$classname = get_called_class();
		$pos = strrpos($classname, '\\');

		if ($pos === false) {
			$this->prefix = 'elbo:rl:'.$classname.':';
		}
		else {
			$this->prefix = 'elbo:rl:'.substr($classname, $pos + 1).':';
		}
	}

	protected function getKey(string $identifier) {
		return $this->prefix.$identifier;
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
