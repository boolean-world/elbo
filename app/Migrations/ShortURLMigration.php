<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class ShortURLMigration extends Migration {
	public function create() {
		Capsule::schema()->create('short_url', function(Blueprint $table) {
			$table->string('shorturl');
			$table->binary('url');
			$table->boolean('custom');
			$table->binary('title')->nullable();
			$table->ipAddress('ip');
			$table->unsignedBigInteger('userid')->nullable();
			$table->boolean('disabled');
			$table->unsignedBigInteger('created_at');

			$table->primary('shorturl');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('short_url');
	}
}
