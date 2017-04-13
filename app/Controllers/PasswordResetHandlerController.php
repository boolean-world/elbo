<?php

namespace Elbo\Controllers;

use ReCaptcha\ReCaptcha;
use Nette\Mail\{Message, IMailer};
use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Library\Controller, Library\Configuration, Models\User, Models\PasswordReset};

class PasswordResetHandlerController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedIn',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);
		$recaptcha = $this->container->get(ReCaptcha::class);

		$email = $request->request->get('email');
		$ip = $request->getClientIp();
		$grecaptcha_resp = $request->request->get('g-recaptcha-response');

		if (!$recaptcha->verify($grecaptcha_resp, $ip)->isSuccess()) {
			return new Response($twig->render('auth/reset.html.twig', [
				'email' => $email,
				'errors' => [
					'captcha' => true
				]
			]));
		}

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			return new Response($twig->render('auth/reset.html.twig', [
				'email' => $email,
				'errors' => [
					'email' => 1
				]
			]));
		}

		$user = User::where('email', $email)->first();

		if ($user === null || $user->disabled) {
			return new Response($twig->render('auth/reset.html.twig', [
				'email' => $email,
				'errors' => [
					'email' => 2
				]
			]));
		}

		PasswordReset::where('userid', $user->id)->delete();

		$token = bin2hex(random_bytes(32));

		PasswordReset::create([
			'token' => $token,
			'userid' => $user->id,
			'expires_at' => time() + 86400
		]);

		$mail = new Message();
		$config = $this->container->get(Configuration::class);
		$mailer = $this->container->get(IMailer::class);

		$host = $request->headers->get('Host');
		$protocol = $request->headers->get('Https') ? 'https' : 'http';

		$mail->setFrom($config->get('mailer.from'))
		     ->addTo($user->email)
		     ->setSubject("elbo.in: Password Reset Request")
		     ->setBody(<<< EOM
Hi,

We recieved a request to reset your password. If you did not ask for a password
reset, you can ignore this mail.

To continue, please copy the link below and open it in your browser:

${protocol}://${host}/~password/reset/${token}

This link will expire in 24 hours.

Regards,
elbo.in admins
EOM
		);

		try {
			$mailer->send($mail);
		}
		catch (\Exception $e) {
			return new Response($twig->render('auth/reset_mail_err.html.twig'), 500);
		}

		return new Response($twig->render('auth/reset_mail.html.twig'));
	}
}
