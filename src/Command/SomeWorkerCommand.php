<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:some-worker')]
class SomeWorkerCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'Timeout in seconds', 60);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeout = $input->getOption('timeout');

        $output->writeln(sprintf("Start doing something important and it could take about %s seconds.", $timeout));
        sleep($timeout);
        $output->writeln("Finished!");

        return self::SUCCESS;
    }
}
