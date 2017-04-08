<?php

namespace Elbo\Models;

use Illuminate\Database\Eloquent\Model;

class ShortURL extends Model {
	protected $table = 'short_url';

	protected $primaryKey = 'shorturl';
	public $incrementing = false;
	public $timestamps = false;

	protected $guarded = [];
}
