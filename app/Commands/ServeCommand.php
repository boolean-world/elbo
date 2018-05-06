<?php

namespace Elbo\Commands;

use Symfony\Component\Console\{Command\Command, Input\InputInterface, Input\InputArgument, Output\OutputInterface};

class ServeCommand extends Command {
	protected function configure() {
		$this->setName('serve')
		     ->setDescription('Serve the application on the PHP development server')
		     ->setHelp('Serve the application on the PHP development server')
		     ->addArgument('port', InputArgument::OPTIONAL, 'The port on which the application should be served.', 8000);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$port = $input->getArgument('port');
		pcntl_exec('/usr/bin/env', ['php', '-S', "0.0.0.0:$port", '-t', 'public', 'public/index.php']);
	}
}
