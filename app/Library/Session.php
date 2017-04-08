<?php

namespace Elbo\Library;

use Elbo\Exceptions\SessionException;

class Session {
	const MAX_PERMITTED_LASTSEEN_RANGE = 608400;
	const prefix = 'elbo:sess:';

	protected $sessionId;
	protected $sessionData;
	protected $redis;
	protected $dirty;

	public function __construct(\Redis $redis) {
		$this->redis = $redis;
		$this->sessionId = null;
		$this->sessionData = [];
		$this->dirty = false;
	}

	public function isStarted() {
		return $this->sessionId !== null;
	}

	public function bind($sessionId) {
		if ($sessionId === null) {
			return false;
		}

		$key = self::prefix.$sessionId;
		$sessionData = $this->redis->get($key);

		if ($sessionData === false) {
			return false;
		}

		$this->redis->setTimeout(self::prefix.$this->sessionId, self::MAX_PERMITTED_LASTSEEN_RANGE);

		$this->sessionData = unserialize($sessionData);
		$this->sessionId = $sessionId;

		return true;
	}

	public function start() {
		if ($this->isStarted()) {
			throw new SessionException('A session is already started.');
		}

		$serialized_sessiondata = serialize([]);

		for ($i = 0; $i < 15; $i++) {
			$sessionId = Base62::encode(random_bytes(32));
			$key = self::prefix.$sessionId;

			if ($this->redis->setNx($key, $serialized_sessiondata)) {
				$this->redis->setTimeout($key, self::MAX_PERMITTED_LASTSEEN_RANGE);
				$this->sessionId = $sessionId;
				$this->dirty = false;

				return;
			}
		}

		throw new SessionException('Failed to create unique session ID.');
	}

	public function regenerate() {
		if (!$this->isStarted()) {
			throw new SessionException('Cannot regenerate a session that has not been started.');
		}

		for ($i = 0; $i < 15; $i++) {
			$sessionId = Base62::encode(random_bytes(32));
			$old_key = self::prefix.$this->sessionId;
			$new_key = self::prefix.$sessionId;

			if ($this->redis->renameNx($old_key, $new_key)) {
				$this->sessionId = $sessionId;
				return;
			}
		}

		throw new SessionException('Failed to regenerate the session.');
	}

	public function destroy() {
		if (!$this->isStarted()) {
			throw new SessionException('Cannot destroy a session that has not been started.');
		}

		$this->redis->del(self::prefix.$this->sessionId);
		$this->sessionData = [];
		$this->sessionId = null;
		$this->dirty = false;
	}

	public function getId() {
		return $this->sessionId;
	}

	public function get($key, $fallback = null) {
		return $this->sessionData[$key] ?? $fallback;
	}

	public function set($key, $value) {
		if (!$this->isStarted()) {
			throw new SessionException('Cannot set data for a session that has not been started.');
		}

		$this->dirty = true;
		$this->sessionData[$key] = $value;
	}

	public function unset($key) {
		if (!$this->isStarted()) {
			throw new SessionException('Cannot set data for a session that has not been started.');
		}

		$this->dirty = true;
		unset($this->sessionData[$key]);
	}

	public function clear() {
		$this->dirty = true;
		$this->sessionData = [];
	}

	public function save() {
		if (!$this->isStarted()) {
			throw new SessionException('Cannot save data for a session that has not been started.');
		}

		$key = self::prefix.$this->sessionId;

		if ($this->dirty) {
			$ttl = $this->redis->ttl($key);
			$this->redis->set($key, serialize($this->sessionData));
			$this->redis->setTimeout($key, $ttl);
			$this->dirty = false;
		}
	}
}
