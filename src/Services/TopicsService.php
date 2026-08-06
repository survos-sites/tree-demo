<?php

namespace App\Services;

use App\Entity\Topic;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TopicsService
{

//    private TopicRepository $topicRepository;

    public function __construct(
        private TopicRepository $topicRepository,
        private ParameterBagInterface $bag,
        private EntityManagerInterface $em,
    private LoggerInterface $logger)
    {
//        $this->topicRepository = $this->em->getRepository(Topic::class);
    }

    public function getTopicCount() {
        return $this->topicRepository->count([]);
    }

    #[AsCommand('app:import-topics', 'Import the IPTC Media Topics concept scheme (cv.iptc.org/newscodes/mediatopic) into Topic')]
    public function importTopicsCommand(
        SymfonyStyle $io,
        #[Argument('Path to a Media Topics JSON export (omit to use the topics_json_file parameter)')]
        ?string $topicsFile = null,
        #[Option('Re-import even if topics already exist')]
        bool $force = false,
    ): int {
        $topicCount = $this->getTopicCount();
        if ($topicCount && !$force) {
            $io->info(sprintf('%d topics already exist.', $topicCount));

            return Command::SUCCESS;
        }

        $topicsJsonFile = $topicsFile ?? $this->bag->get('kernel.project_dir') . '/cptall-en-US.json';
        assert(file_exists($topicsJsonFile));
        $this->importTopics($topicsJsonFile);

        $io->success(sprintf('%d topics imported.', $this->getTopicCount()));

        return Command::SUCCESS;
    }

    public function importTopics(?string $topicsJsonFile=null)
    {
        if (!$topicsJsonFile) {

            // https://cv.iptc.org/newscodes/mediatopic/?lang=en-US&format=json
            $topicsJsonFile = $this->bag->get('topics_json_file');
        }

        $this->em->createQuery("delete from " . Topic::class)->execute();

        $data = json_decode(file_get_contents($topicsJsonFile))->conceptSet;
        $topics = [];
        foreach ($data as $cSet) {
            $topicCode = $this->getCode($cSet->qcode, ':');
            $topic = (new Topic())
                ->setCode($topicCode)
//                ->setChildCount(count($cSet->narrower))
                ->setName($cSet->prefLabel->{'en-US'}??'no-name');
            $topic
                ->setDescription($cSet->definition->{'en-US'}??"no description");
            $this->em->persist($topic);
            $topics[$topicCode] = $topic;

            // not in order, e.g. 20001181, so defer until later
//            if (isset($cSet->broader)) {
//                $parentCode = $this->getCode($cSet->broader[0], '/');
//                assert(array_key_exists($parentCode, $topics), "Missing $parentCode");
//                $topics[$parentCode]->addChild($topic);
//            }

        }
        $this->logger->info("Topics loaded, now setting parents");

        reset($data);
        foreach ($data as $cSet) {
            $topicCode = $this->getCode($cSet->qcode, ':');
            $topic = $topics[$topicCode];
            if (isset($cSet->broader)) {
                $parentCode = $this->getCode($cSet->broader[0], '/');
                $topics[$parentCode]->addChild($topic);
//                $topic->setParent($topics[$parentCode]);
            } else {
//                $topic->setParent(null);
            }
        }
        $this->logger->info("Flushing...");
        $this->em->flush();

//        foreach ($data->hasTopConcept as $topConcept) {
//            $code = explode('/', $topConcept);
//            $numericCode = $code[5];
//            dd($topic[$numericCode]);
//        }
    }

    private function getCode(string $string, string $delimiter = '/'): string
    {
        return current(array_slice(explode($delimiter, $string), -1));
    }


}
