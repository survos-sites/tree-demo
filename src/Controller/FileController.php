<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route('/file-overview', name: 'app_file_overview')]
    public function index(): Response
    {
        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
        ]);
    }

    #[Route(path: '/repo-files', name: 'app_file')]
    public function files(Request $request, string $entity)
    {
        $repo = match($entity) {
            'files' => $this->fileRepository,
            'topics' => $this->topicRepository
        };

        if (0)
            $htmlTree = $repo->childrenHierarchy(
                null, /* starting from root nodes */
                false, /* false: load all children, true: only direct */
                array(
                    'decorate' => true,
                    'representationField' => 'name',
                    'html' => true,
                    'nodeDecorator' => function ($node)
                    {
                        return sprintf("%s %s %s", $node['name'], $node['code'], $node['lvl']);
                    },
                ),
                true
            );
        return $this->render('file/show.html.twig', [
            'entity' => $entity,
            'entities' => $repo->findBy(['level' => 0]),
            'html' => ''// $htmlTree
        ]);
    }
}
