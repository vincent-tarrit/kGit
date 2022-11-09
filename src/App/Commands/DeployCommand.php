<?php

namespace Console\App\Commands;

use Console\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeployCommand extends Command
{
    protected function configure()
    {
        $this->setName('deploy')
            ->setDescription('Deploys the application.')
            ->setHelp('Allows you to deploy the application on Infomaniak servers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = $this->getHelper('question');
        $config = (new Config($input, $output, $question))->init();

        $output->writeln('Deploying...');

        exec("git add .");
        exec("git commit -m 'Deploy'");

        //exec changing branch to deploy
        exec("git checkout deploy");

        //exec merging main to deploy
        exec("git merge main");

        exec("git push -u origin deploy");

        //exec changing branch to main
        exec("git checkout main");


        return Command::SUCCESS;
    }
}
