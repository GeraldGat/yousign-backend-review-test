<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\GithubEventsImporter;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events'
)]
class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private ValidatorInterface $validator,
        private GithubEventsImporter $gitHubEventImporter
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The location or url of the file from which to import the events')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->gitHubEventImporter->importFromFile(source: $input->getArgument('file'), onProgress: function() use ($io) {
                $memoryUsageInMB = memory_get_usage(true) / 1024 / 1024;
                $peakMemoryInMB = memory_get_peak_usage(true) / 1024 / 1024;
                $io->section("A batch has been completed");
                $io->text("Memory usage: $memoryUsageInMB MB, Peak memory usage: $peakMemoryInMB MB");
                $io->text("Object in memory: " . count(get_declared_classes()));
            });
        } catch (Exception $e) {
            $io->error('An error occurred while importing the events: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
