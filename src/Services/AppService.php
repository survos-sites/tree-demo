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
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager,
                                private FileRepository $fileRepository,
                                LoggerInterface        $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function importDirectory(string $directory, array $options = []): void
    {

        $em = $this->entityManager;
//        $root = (new File())
//            ->setIsDir(true)
//            ->setName($directory);
//        $em->persist($root);
        $root = null; // can't figure out how to only open the top level, so this is a hack.
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
                ->setName($name);

            if ($parentName = $fileInfo->getRelativePath()) {
                // symbolic links, like base-bundle, don't work right
                if (!array_key_exists($parentName, $dirs)) {
                    continue;
                }
                assert(array_key_exists($parentName, $dirs), sprintf("Missing %s in %s (%s)", $parentName, $fileInfo->getPathname(), $directory));
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
                    ->setName($fileInfo->getFilename());
                $this->logger->info(sprintf("Adding %s to %s", $f->getName(), $f->getParent()));
            }
        }
        $em->flush();

    }


}
