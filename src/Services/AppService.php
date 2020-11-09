<?php

namespace App\Services;

use App\Entity\File;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

class AppService
{
    private EntityManagerInterface $entityManager;
    private FileRepository $fileRepository;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {

        $this->entityManager = $entityManager;
        $this->fileRepository = $entityManager->getRepository(File::class);
        $this->logger = $logger;
    }

    public function importDirectory(string $directory, array $options = []): void
    {

        $em = $this->entityManager;
        $root = (new File())
            ->setIsDir(true)
            ->setName($directory);
        $em->persist($root);
        $finder = new Finder();
        $finder
            ->ignoreVCSIgnored(true)
            ->in($directory);
//        foreach ($finder->directories() as $directory) {
//            dd($directory);
//        }
//        dd($finder->directories());

        // could do this by root only, too.
        $query = $em->createQuery(
            sprintf('DELETE FROM %s e', File::class)
        )->execute();

        $dir = null;
        $dirs = [];
        foreach ($finder as $fileInfo) {
            $name = $fileInfo->getFilename();
            $f = (new File())
                ->setIsDir($fileInfo->isDir())
                ->setName($name)
            ;
            ;

            if ($parentName = $fileInfo->getRelativePath()) {
//                dd($fileInfo, $parentName, $dirs[$parentName]);
                $dir = $dirs[$parentName];
            } else {
                $dir = $root;
            }
            $f->setParent($dir);
//            dd($fileInfo, $parentName );

            $em->persist($f);
            if ($fileInfo->isDir()) {
                $dirs[$fileInfo->getRelativePathname()] = $f;
                $f
//                    ->setName($fileInfo->getFilename())
                    ->setIsDir(true);
                $this->logger->warning("Directory", [$f->getName(), $parentName]);
            } else {
                $f
                    ->setIsDir(false)
                    ->setName($fileInfo->getFilename())
                ;
                $this->logger->info(sprintf("Adding %s to %s", $f->getName(), $f->getParent()));
            }
        }
        $em->flush();

    }


}
