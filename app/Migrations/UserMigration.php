<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class UserMigration extends Migration {
	public function create() {
		Capsule::schema()->create('users', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('email')->unique();
			$table->string('normalized_email')->unique();
			$table->string('password');
			$table->ipAddress('created_from');
			$table->unsignedBigInteger('created_at');
			$table->ipAddress('last_login_ip')->nullable();
			$table->unsignedBigInteger('last_login')->nullable();
			$table->boolean('admin');
			$table->boolean('disabled');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('users');
	}
}
