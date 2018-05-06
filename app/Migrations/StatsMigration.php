<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class StatsMigration extends Migration {
	public function create() {
		Capsule::schema()->create('stats', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('shorturl', 70);
			$table->unsignedSmallInteger('year');
			$table->unsignedTinyInteger('month');
			$table->unsignedTinyInteger('date');
			$table->string('browser', 80)->nullable();
			$table->string('platform', 80)->nullable();
			$table->string('country')->nullable();
			$table->string('referer')->nullable();
			$table->unsignedBigInteger('count');

			$table->index(['shorturl', 'year', 'month', 'date', 'browser', 'platform', 'country', 'referer'], 'index_001');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('stats');
	}
}
