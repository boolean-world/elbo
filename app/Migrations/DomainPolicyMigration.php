<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class DomainPolicyMigration extends Migration {
	public function create() {
		Capsule::schema()->create('domain_policy', function(Blueprint $table) {
			$table->string('domain');
			$table->boolean('automated');
			$table->integer('policy');
			$table->binary('comment')->nullable();

			$table->primary('domain');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('domain_policy');
	}
}
