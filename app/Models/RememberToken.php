<?php

namespace Elbo\Models;

use Elbo\Library\Base62;
use Illuminate\Database\Eloquent\Model;

class RememberToken extends Model {
	protected $table = 'remember_token';
	protected $primaryKey = 'authenticator';

	public $timestamps = false;
	public $incrementing = false;

	public static function createFor($userid) {
		$token = Base62::encode(random_bytes(32));

		$tokeninfo = new self();
		$tokeninfo->authenticator = hash('sha256', $token);
		$tokeninfo->userid = $userid;
		$tokeninfo->created_at = time();
		$tokeninfo->save();

		return $token;
	}
}
