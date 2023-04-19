<?php

namespace App\Command;

use App\Entity\File;
use App\Repository\FileRepository;
use App\Services\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
#[AsCommand( 'app:load-directory-files', description: 'Import a directory into a nested tree')]
class LoadDirectoryFilesCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em,
                                private readonly ParameterBagInterface $bag,
                                private readonly AppService $appService, string $name = null)
    {
        parent::__construct($name);

    }

    protected function configure()
    {
        $this
            ->addArgument('dir', InputArgument::OPTIONAL, 'path to directory root',  $this->bag->get('kernel.project_dir'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('dir');

        $this->appService->importDirectory($directory);
        $this->em->flush();
        $io->success('Import complete');

        return Command::SUCCESS;
    }

}
