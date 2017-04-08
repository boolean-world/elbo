<?php

namespace Elbo\Models;

use Illuminate\Database\Eloquent\Model;

class ShortenHistory extends Model {
	protected $table = 'shorten_history';
	public $timestamps = false;
	protected $guarded = [];
}
