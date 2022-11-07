<?php

namespace Console\Config;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class Config
{

    public function __construct(private $input, private $output, private $question)
    {
    }

    public function init()
    {
        $this->output->writeln('Checking configuration...');
        $res = exec('pwd');

        $finder = new Finder();

        $files = $finder->files()->in($res)->name('ik_config.json');
        foreach ($finder as $file) {
            break;
        } // get first file

        $config = [];

        if ($finder->hasResults() && $this->isCorrectConfig($file)) {
            $this->output->writeln('Configuration file found');
            $config = json_decode(file_get_contents($res . '/ik_config.json'), true);
        } else {
            if ($finder->hasResults()) {
                $this->output->writeln('Configuration file is not correct');
            } else {
                $this->output->writeln('Configuration file not found');
            }

            $username = $this->question->ask($this->input, $this->output, new Question('Infomaniak SSH username (abcde_12345) : ', ''));
            $user_path = $this->question->ask($this->input, $this->output, new Question('Infomaniak SSH user path (/home/clients/1234567891234566789) : ', ''));
            $ssh_key_path = $this->question->ask($this->input, $this->output, new Question('Infomaniak SSH key path [~/.ssh/id_ed25519] : ', '~/.ssh/id_ed25519'));
            $key_phrase = $this->question->ask($this->input, $this->output, new Question('Infomaniak SSH key passphrase (leave empty if no passphrase) :', ''));
            $repository_name = $this->question->ask($this->input, $this->output, new Question('Repository name : ', 'Repository_name' . date('Y-m-d_H-i-s')));
            $domain = $this->question->ask($this->input, $this->output, new Question('Domain name : ', 'repository_name.com'));

            $config = [
                'user' => explode('_', $username)[0],
                'username' => $username,
                'user_path' => $user_path,
                'ssh_key_path' => $ssh_key_path,
                'key_phrase' => $key_phrase,
                'repository_name' => $repository_name,
                'domain' => $domain,
            ];

            $configJson = json_encode($config, JSON_PRETTY_PRINT);

            file_put_contents('ik_config.json', $configJson);
        }

        return $config;
    }

    private function isCorrectConfig($file)
    {
        $config = json_decode(file_get_contents($file), true);

        return isset($config['user'])
            && isset($config['username'])
            && isset($config['user_path'])
            && isset($config['ssh_key_path'])
            && isset($config['key_phrase'])
            && isset($config['repository_name'])
            && isset($config['domain']);
    }
}
