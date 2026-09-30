<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Topic;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\MediaTopics\MediaTopics;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TopicsService
{
    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly EntityManagerInterface $em,
        private readonly MediaTopics $mediaTopics,
    ) {}

    #[AsCommand('app:import-topics', 'Load the IPTC Media Topics tree from survos/media-topics-bundle')]
    public function importTopicsCommand(
        SymfonyStyle $io,
        #[Option('Replace the topics already loaded')] bool $force = false,
    ): int {
        if (($count = $this->getTopicCount()) && !$force) {
            $io->info(sprintf('%d topics already exist.', $count));

            return Command::SUCCESS;
        }
        $this->importTopics();
        $io->success(sprintf('%d topics imported (IPTC Media Topics %s).', $this->getTopicCount(), $this->mediaTopics->version()));

        return Command::SUCCESS;
    }

    public function getTopicCount(): int
    {
        return $this->topicRepository->count([]);
    }

    /** Replaces the topic table with the topics currently in use; retired ones are left out of the demo tree. */
    public function importTopics(): void
    {
        $this->em->createQuery('delete from '.Topic::class)->execute();

        $topics = [];
        // Parents come before their children, so each parent is already persisted.
        foreach ($this->mediaTopics as $id => $mediaTopic) {
            $topic = (new Topic())
                ->setCode((string) $id)
                ->setName($mediaTopic->label('en-US'))
                ->setDescription($mediaTopic->definition('en-US') ?: 'no description');
            if ($mediaTopic->parentId !== null) {
                $topic->setParent($topics[$mediaTopic->parentId]);
            }
            $this->em->persist($topic);
            $topics[$id] = $topic;
        }
        $this->em->flush();
    }
}
