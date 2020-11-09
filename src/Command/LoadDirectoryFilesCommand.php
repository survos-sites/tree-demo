<?php

namespace App\Command;

use App\Entity\File;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class LoadDirectoryFilesCommand extends Command
{
    protected static $defaultName = 'app:load-directory-files';
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var FileRepository
     */
    private $fileRepository;

    public function __construct(EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;

        $this->fileRepository = $em->getRepository(File::class);
    }

    protected function configure()
    {
        $this
            ->setDescription('Import a directory into a nested tree')
            ->addArgument('dir', InputArgument::REQUIRED, 'path to directory root')
            ->addOption('gitignore', null, InputOption::VALUE_NONE, 'Ignore .gitignore files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('dir');

        $finder = new Finder();
        $finder
//            ->files()
            ->ignoreVCSIgnored($input->getOption('gitignore'))
            ->in($directory)
        ;


        $dir = null;
        $files = [];
        foreach ($finder as $fileInfo) {
            $f = (new File())
                ->setName($fileInfo->getPathname());
            $this->em->persist($f);
            if ($fileInfo->isDir()) {
                $f
                    ->setIsDir(true);
                $dir = $f;
                continue;
            }
            $f
                ->setParent($dir) // assuming that the results are in order
                ->setIsDir($fileInfo->isDir())
                ->setName($fileInfo->getFilename());
            $io->info(sprintf("Adding %s to %s", $f->getName(), $f->getParent()));
            $parentName = $fileInfo->getPath();
        }
        $this->em->flush();
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
