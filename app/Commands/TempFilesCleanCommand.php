<?php

namespace Elbo\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class TempFilesCleanCommand extends Command {
	protected function configure() {
		$this->setName('clean:temporary')
		     ->setDescription('Remove temporary files')
		     ->setHelp('Remove temporary files');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$fs = new Filesystem();
		$path = 'data/tmp/';

		if ($fs->exists($path)) {
			$fs->remove(glob("$path/*"));
		}

		$output->writeln('Temporary files removed.');
	}
}
