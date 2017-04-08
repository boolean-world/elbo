<?php

use Elbo\Library\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

function bootstrap_eloquent(Configuration $config) {
	$capsule = new Capsule();
	$capsule->addConnection($config->get('database'));
	$capsule->setAsGlobal();
	$capsule->bootEloquent();
}
