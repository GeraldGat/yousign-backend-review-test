<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\ImportGithubEventsCommandInput;
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
    name: 'app:import-github-events-from-archive',
    description: 'Import GH events from GitHub archive'
)]
class ImportGitHubEventsFromGitHubArchiveCommand extends Command
{
    public function __construct(
        private ValidatorInterface $validator,
        private GithubEventsImporter $gitHubEventImporter,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, 'The date of the events to import')
            ->addArgument('hour', InputArgument::REQUIRED, 'The hour of the events to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Validation of arguments
        $importOptions = new ImportGithubEventsCommandInput(
            $input->getArgument('date'),
            (int) $input->getArgument('hour')
        );

        $violations = $this->validator->validate($importOptions);
        if(count($violations) > 0) {
            foreach($violations as $violation) {
                $io->error($violation->getMessage());
            }
            return Command::FAILURE;
        }

        // Github archive url of the file to import
        $githubArchiveUrl = "https://data.gharchive.org/{$importOptions->date}-{$importOptions->hour}.json.gz";

        try {
            $this->gitHubEventImporter->importFromFile(source: $githubArchiveUrl, onProgress: function() use ($io) {
                $memoryUsageInMB = memory_get_usage(true) / 1024 / 1024;
                $peakMemoryInMB = memory_get_peak_usage(true) / 1024 / 1024;
                $io->info("Memory usage: $memoryUsageInMB MB, Peak memory usage: $peakMemoryInMB MB");
            });
        } catch (Exception $e) {
            $io->error('An error occurred while importing the events: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
