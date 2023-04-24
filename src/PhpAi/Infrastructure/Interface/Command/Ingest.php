<?php

namespace Obokaman\PhpAi\Infrastructure\Interface\Command;

use Obokaman\PhpAi\Service\Ai;
use Obokaman\PhpAi\Service\Document\FolderParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Ingest extends Command
{
    public function __construct(private Ai $ai, private FolderParser $folder_parser)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('memory:ingest')
             ->setDescription('Ingest initial data to AI memory.')
             ->setHelp('This command ingest initial data to AI memory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $console = new SymfonyStyle($input, $output);

        $documents = $this->folder_parser->parse('./public/docs_to_ingest');

        $console->writeln('Memorizing ' . count($documents) . ' document/s...');

        $this->ai->memorize($documents);

        $console->success('DONE');

        return Command::SUCCESS;
    }
}
