<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class RememberTokenMigration extends Migration {
	public function create() {
		Capsule::schema()->create('remember_token', function(Blueprint $table) {
			$table->string('authenticator');
			$table->unsignedBigInteger('userid');
			$table->unsignedBigInteger('created_at');

			$table->primary('authenticator');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('remember_token');
	}
}
