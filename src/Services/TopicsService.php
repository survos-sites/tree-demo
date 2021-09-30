<?php

namespace App\Services;

use App\Entity\Topic;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TopicsService
{

    private TopicRepository $topicRepository;

    public function __construct(private EntityManagerInterface $em,
    private LoggerInterface $logger)
    {
        $this->topicRepository = $this->em->getRepository(Topic::class);
    }

    public function getTopicCount() {
        return $this->topicRepository->count([]);
    }

    public function importTopics(string $topicsJsonFile)
    {
        $this->em->createQuery("delete from " . Topic::class)->execute();

        $data = json_decode(file_get_contents($topicsJsonFile))->conceptSet;
        $topics = [];
        foreach ($data as $cSet) {
            $topicCode = $this->getCode($cSet->qcode, ':');
            $topic = (new Topic())
                ->setCode($topicCode)
                ->setName($cSet->prefLabel->{'en-US'})
                ->setDescription($cSet->definition->{'en-US'});
            $this->em->persist($topic);
            $topics[$topicCode] = $topic;
        }
        $this->em->flush();
        $this->logger->warning("Topics loaded and flushed without parents");

        reset($data);
        foreach ($data as $cSet) {
            $topicCode = $this->getCode($cSet->qcode, ':');
            $topic = $topics[$topicCode];
            if (isset($cSet->broader)) {
                $parentCode = $this->getCode($cSet->broader[0], '/');
                $this->logger->warning("Parent", [$parentCode, $topicCode]);
//                assert(array_key_exists($parentCode, $parents), "Missing $parentCode as parent");
                $topic->setParent($topics[$parentCode]);
            } else {
//                $topic->setParent(null);
            }
            if ($topic->getName() == 'civil rights') {
//                dd($parentCode, $topicCode, $topic->getParent());
            }
        }
        $this->logger->warning("Flushing...");
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
