<?php

namespace Elbo\Commands;

use Elbo\{Library\Email, Models\User};
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface, Question\Question, Helper\QuestionHelper};

class CreateAdminCommand extends Command {
	protected function configure() {
		$this->setName('create-admin')
		     ->setDescription('Create an administrator account')
		     ->setHelp('Create an administrator account');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$helper = new QuestionHelper();

		$emailQuestion = new Question('E-Mail address: ');
		$emailQuestion->setMaxAttempts(3);
		$emailQuestion->setNormalizer(function($value) {
			return trim($value);
		});
		$emailQuestion->setValidator(function($value) {
			if (User::where('normalized_email', Email::normalize($value))->count() !== 0) {
				throw new \RuntimeException("This e-mail address is already registered.");
			}

			return $value;
		});

		$passwordQuestion = new Question('Password: ');
		$passwordQuestion->setHidden(true);
		$passwordQuestion->setMaxAttempts(3);
		$passwordQuestion->setValidator(function($value) {
			if (strlen($value) < 6) {
				throw new \RuntimeException("The password must be at least 6 characters long.");
			}

			return $value;
		});

		$email = $helper->ask($input, $output, $emailQuestion);
		$password = $helper->ask($input, $output, $passwordQuestion);
		$time = time();

		User::create([
			'email' => $email,
			'normalized_email' => Email::normalize($email),
			'password' => password_hash($password, PASSWORD_DEFAULT),
			'created_from' => '127.0.0.1',
			'created_at' => $time,
			'admin' => true,
			'disabled' => false
		]);
	}
}
