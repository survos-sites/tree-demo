<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TopicsPlaygroundController extends AbstractController
{
    #[Route('/playground/topics', name: 'topics_playground', methods: ['GET'])]
    public function __invoke(Request $request, TopicRepository $topics): Response
    {
        $backend = $request->query->getString('backend', 'ux');
        if (!in_array($backend, ['ux', 'simple', 'plain'], true)) {
            throw $this->createNotFoundException('Unknown table backend.');
        }

        return $this->render('playground/topics.html.twig', [
            'backend' => $backend,
            'topics' => $topics->createQueryBuilder('topic')
                ->leftJoin('topic.parent', 'parent')->addSelect('parent')
                ->orderBy('topic.name', 'ASC')
                ->getQuery()->getResult(),
        ]);
    }
}
