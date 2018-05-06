<?php

namespace Elbo\Commands;

use Symfony\Component\Console\{Command\Command, Input\InputInterface, Input\InputArgument, Input\ArrayInput, Output\OutputInterface};

class CleanCommand extends Command {
	protected function configure() {
		$this->setName('clean:all')
		     ->setDescription('Clean caches and temporary data stores')
		     ->setHelp('Clean caches and temporary data stores');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$app = $this->getApplication();
		$emptyargs = new ArrayInput([]);

		$app->find('clean:temporary')->run($emptyargs, $output);
		$app->find('clean:routes')->run($emptyargs, $output);
		$app->find('clean:config')->run($emptyargs, $output);
		$app->find('clean:templates')->run($emptyargs, $output);
		$app->find('clean:container')->run($emptyargs, $output);
	}
}
