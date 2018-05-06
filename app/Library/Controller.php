<?php

namespace Elbo\Library;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Controller {
	protected $request;
	protected $data;
	protected $container;
	protected $middlewares = [];

	private $currentMiddlewareIndex = 0;
	private $runInvoked = false;

	public final function __construct(Request $request, array $data, ContainerInterface $container) {
		$this->request = $request;
		$this->data = $data;
		$this->container = $container;

		$this->initialize();
	}

	protected function initialize() {}

	abstract public function run(Request $request, array &$data);

	protected final function next() {
		$currentMiddleware = $this->middlewares[$this->currentMiddlewareIndex] ?? null;

		if ($currentMiddleware === null) {
			if ($this->runInvoked) {
				throw new \LogicException('No next layer to run.');
			}

			$this->runInvoked = true;
			return $this->run($this->request, $this->data);
		}

		$this->currentMiddlewareIndex++;
		return $this->$currentMiddleware($this->request);
	}

	public final function start() {
		return $this->next();
	}
}
