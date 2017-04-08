<?php

namespace Elbo\Migrations;

use Illuminate\Database\{Schema\Blueprint, Migrations\Migration, Capsule\Manager as Capsule};

class StatsMigration extends Migration {
	public function create() {
		Capsule::schema()->create('stats', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('shorturl');
			$table->unsignedSmallInteger('year');
			$table->unsignedTinyInteger('month');
			$table->unsignedTinyInteger('date');
			$table->string('browser')->nullable();
			$table->string('platform')->nullable();
			$table->string('country')->nullable();
			$table->string('referer')->nullable();
			$table->unsignedBigInteger('count');
		});
	}

	public function delete() {
		Capsule::schema()->dropIfExists('stats');
	}
}
