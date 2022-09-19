<?php

// see https://symfony.com/blog/new-in-symfony-5-1-single-command-applications
// and https://github.com/zenstruck/console-extra

namespace App\Command;

use App\Services\TopicsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:import-topics',
    description: 'Add a short description for your command',
)]
class ImportTopicsCommand extends Command
{
    public function __construct(private readonly TopicsService $topicsService,
                                private readonly ParameterBagInterface $bag,
                                string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('topicsFile', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force reload')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $topicCount = $this->topicsService->getTopicCount();
        if ( $topicCount && !$force) {
            $io->info(sprintf("%d topics already exist.", $topicCount));
            return self::SUCCESS;
        }

        if (!$topicsJsonFile = $input->getArgument('topicsFile')) {
            $topicsJsonFile = $this->bag->get('kernel.project_dir') . '/cptall-en-US.json';
        }

        assert(file_exists($topicsJsonFile));
        $this->topicsService->importTopics($topicsJsonFile);

        $io->success(sprintf("%d topics imported.", $this->topicsService->getTopicCount()));

        return Command::SUCCESS;
    }
}
