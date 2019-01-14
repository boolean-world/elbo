<?php

return function(FastRoute\RouteCollector $r) {
	$r->get('/', 'IndexController');
	$r->post('/~shorten', 'ShortenController');
	$r->get('/~rl', 'RateLimitController');
	$r->get('/{shorturl:[A-Za-z0-9-]{1,70}}', 'RedirectController');
	$r->get('/~qr/{shorturl:[A-Za-z0-9-]{1,70}}', 'QRCodeController');
	$r->get('/~qr/img/{shorturl:[A-Za-z0-9-]{1,70}}', 'QRImageController');

	// Controllers for the registered users.
	$r->get('/~home', 'HomeController');
	$r->get('/~login', 'LoginPageController');
	$r->post('/~login', 'LoginHandlerController');
	$r->get('/~logout', 'LogoutController');
	$r->get('/~signup', 'RegisterPageController');
	$r->post('/~signup', 'RegisterHandlerController');
	$r->get('/~account', 'AccountController');
	$r->post('/~account', 'AccountController');
	$r->post('/~account/delete', 'AccountDeleteController');
	$r->get('/~history', 'HistoryController');
	$r->get('/~export', 'ExportController');
	$r->get('/~password/reset', 'PasswordResetPageController');
	$r->post('/~password/reset', 'PasswordResetHandlerController');
	$r->post('/~password/reset/{token}', 'PasswordResetChangeController');
	$r->get('/~password/reset/{token}', 'PasswordResetTokenController');
	$r->get('/~analytics/{shorturl:[A-Za-z0-9-]{1,70}}', 'AnalyticsController');
	$r->get('/~analytics/data/{shorturl:[A-Za-z0-9-]{1,70}}/{duration:week|month|year}', 'AnalyticsDataController');

	// Admin panel.
	$r->get('/~admin', 'Admin\InitController');

	$r->get('/~admin/shorturls', 'Admin\ShortURLController');
	$r->get('/~admin/shorturl/delete/{shorturl:[A-Za-z0-9-]{1,70}}', 'Admin\ShortURLDeleteController');
	$r->get('/~admin/shorturl/enable/{shorturl:[A-Za-z0-9-]{1,70}}', 'Admin\ShortURLEnableController');
	$r->get('/~admin/shorturl/disable/{shorturl:[A-Za-z0-9-]{1,70}}', 'Admin\ShortURLDisableController');

	$r->get('/~admin/policies', 'Admin\PoliciesController');
	$r->get('/~admin/policy/new', 'Admin\NewPolicyController');
	$r->post('/~admin/policy/new', 'Admin\NewPolicyHandlerController');
	$r->get('/~admin/policy/edit/{domain}', 'Admin\EditPolicyController');
	$r->post('/~admin/policy/edit/{domain}', 'Admin\EditPolicyHandlerController');
	$r->get('/~admin/policy/delete/{domain}', 'Admin\DeletePolicyController');

	$r->get('/~admin/users', 'Admin\UsersController');
	$r->get('/~admin/user/delete/{userid:[0-9]+}', 'Admin\UserDeleteController');
	$r->get('/~admin/user/enable/{userid:[0-9]+}', 'Admin\UserEnableController');
	$r->get('/~admin/user/disable/{userid:[0-9]+}', 'Admin\UserDisableController');
};
