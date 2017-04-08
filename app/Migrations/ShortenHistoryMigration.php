<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class ShortenHistoryMigration extends Migration {
	public function create() {
		Capsule::schema()->create('shorten_history', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('shorturl');
			$table->unsignedBigInteger('userid');
			$table->unsignedBigInteger('created_at');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('shorten_history');
	}
}
