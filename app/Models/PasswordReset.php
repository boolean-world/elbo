<?php

namespace Elbo\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model {
	protected $table = 'password_reset';

	protected $primaryKey = 'token';
	public $incrementing = false;
	public $timestamps = false;

	protected $guarded = [];
}
