<?php

namespace Console\App\Commands;

use Console\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class InitCommand extends Command
{
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initializes the application.')
            ->setHelp('Allows you to initialize the application on Infomaniak servers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Application is initializing.');
        $question = $this->getHelper('question');

        $config = (new Config($input, $output, $question))->init();

        dump($config);

        $output->writeln('Config initialized.');

        $output->writeln('Creating resources...');

        exec('git init');
        $hooks = $this->writeHook($config);

        dump($hooks);


        exec(`ssh -i {$config['ssh_key_path']} {$config['username']}@{$config['user']}.ftp.infomaniak.com "mkdir -p {$config['user_path']}/repositories/{$config['repository_name']} && cd {$config['user_path']}/repositories/{$config['repository_name']} && git init --bare && cd hooks && touch post-receive && chmod +x post-receive && echo '{$hooks}' >> post-receive"`);
        $cmd = "git remote add origin {$config['username']}@{$config['user']}.ftp.infomaniak.com:{$config['user_path']}/repositories/{$config['repository_name']}";
        exec($cmd);


        exec("git add .");
        exec("git commit -m 'Initial commit'");
        exec("git push -u origin main");

        exec("git checkout -b deploy");
        exec("git push -u origin deploy");
        exec("git checkout main");


        $output->writeln('Resources created.');
        return Command::SUCCESS;
    }

    private function writeHook($config)
    {
        return '
            #!/usr/bin/env bash
            set -e
            
            echo Checking out latest version...
            export GIT_DIR=' . $config['user_path'] . '/repositories/' . $config["repository_name"] . '
            export GIT_WORK_TREE=' . $config['user_path'] . '/sites/' . $config["domain"] . '
            git checkout -f deploy
            cd "\$GIT_WORK_TREE"
            
            echo Deployment successful            
            ';
    }
}
