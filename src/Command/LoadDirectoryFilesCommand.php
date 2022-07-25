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
use Symfony\Component\Finder\Finder;
#[AsCommand( 'app:load-directory-files', description: 'Import a directory into a nested tree')]
class LoadDirectoryFilesCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var FileRepository
     */
    private $fileRepository;
    /**
     * @var AppService
     */
    private AppService $appService;

    public function __construct(EntityManagerInterface $em, AppService $appService, string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;

        $this->fileRepository = $em->getRepository(File::class);
        $this->appService = $appService;
    }

    protected function configure()
    {
        $this
            ->addArgument('dir', InputArgument::REQUIRED, 'path to directory root')
            ->addOption('gitignore', null, InputOption::VALUE_NONE, 'Ignore .gitignore files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('dir');

        $this->appService->importDirectory($directory, ['gitignore' => $input->getOption('gitignore')]);
        $this->em->flush();
        $io->success('Import complete');

        return Command::SUCCESS;
    }

}
