<?php

namespace Obokaman\PhpAi\Infrastructure\Interface\Command;

use Obokaman\PhpAi\Service\Ai;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Purge extends Command
{
    public function __construct(private Ai $ai)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('memory:purge')
             ->setDescription('Purge AI memory.')
             ->setHelp('This command clean & purge AI memory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $console = new SymfonyStyle($input, $output);

        if (false === $console->confirm('Should I purge the AI memory?', false)) {
            return Command::SUCCESS;
        }

        $this->ai->forgetEverything();

        $console->success('Memory purged correctly.');

        return Command::SUCCESS;
    }
}
