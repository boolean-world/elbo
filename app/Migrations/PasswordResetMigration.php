<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class PasswordResetMigration extends Migration {
	public function create() {
		Capsule::schema()->create('password_reset', function(Blueprint $table) {
			$table->string('token');
			$table->unsignedBigInteger('userid');
			$table->unsignedBigInteger('expires_at');

			$table->primary('token');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('password_reset');
	}
}
