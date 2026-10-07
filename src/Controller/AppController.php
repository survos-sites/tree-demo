<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\File;
use App\Entity\Location;
use App\Repository\FileRepository;
use App\Repository\LocationRepository;
use App\Repository\TopicRepository;
use App\Services\AppService;
use App\Services\TopicsService;
use Doctrine\ORM\EntityManagerInterface;
use Survos\CoreBundle\Traits\JsonResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    use JsonResponseTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileRepository $fileRepository,
        private readonly TopicRepository $topicRepository,
        private readonly LocationRepository $locationRepository,
        private readonly TopicsService $topicsService,
        private readonly AppService $appService,
        private readonly ParameterBagInterface $bag,
    ) {}

    #[Route(path: '/load-topics', name: 'app_load_topics')]
    public function loadTopics(): Response
    {
        $this->topicsService->importTopics();
        return $this->redirectToRoute('topic_index', ['entity' => 'topics']);
    }

    #[Route(path: '/load-files', name: 'app_load_files')]
    public function loadFiles(): Response
    {
        $directory = $this->bag->get('kernel.project_dir');
        $this->appService->importDirectory($directory);
        return $this->redirectToRoute('app_repo_files', ['entity' => 'files']);
    }

    #[Route(path: '/file-source', name: 'app_file_source')]
    public function fileSource(Request $request): Response
    {
        $directory = $this->bag->get('kernel.project_dir');
        $filename = $directory . '/' . $request->get('path');
        assert(file_exists($filename), "file $filename does not exist.");
        // limit to text files?
        $contents = file_get_contents($filename);
        return new Response($contents);
    }

    #[Route(path: '/knp-menu', name: 'app_knp_menu')]
    public function knpMenu(): Response
    {
        return $this->render('menu.html.twig');
    }

    #[Route(path: '/basic-{entity}', name: 'app_tree_entity')]
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
            'fileClass' => File::class,
            'entities' => $repo->findBy(['level' => 0]),
            'html' => ''// $htmlTree
        ]);
    }

    #[Route(path: '/html-demos', name: 'app_basic_html')]
    public function html(): Response
    {
        $count = $this->topicRepository->count([]);
        return $this->render('app/basic-html.html.twig', []);
    }

    #[Route(path: '/module', name: 'app_module')]
    public function module(): Response
    {
        return $this->render('app/module.html.twig', []);
    }

    #[Route(path: '/twig-browser-demo', name: 'app_twig_browser_demo')]
    public function twigBrowserDemo(): Response
    {
        return $this->render('app/twig-browser-demo.html.twig', []);
    }

    #[Route(path: '/', name: 'app_homepage', options: ['expose' => true])]
    public function home()
    {
        return $this->render('app/home.html.twig', ['topicCount' => $this->topicRepository->count([])]);
    }

    private function getSampleJson() {
        return file_get_contents(__DIR__ . '/../../public/alt-format.json');
        return file_get_contents(__DIR__ . '/../../public/tree.json');
        return '[{"id":"j1_1","text":"Basement","icon":true,"li_attr":{"id":"j1_1"},"a_attr":{"href":"#","id":"j1_1_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_2","text":"1FL\\First Floor","icon":true,"li_attr":{"id":"j1_2"},"a_attr":{"href":"#","id":"j1_2_anchor"},"state":{"loaded":true,"opened":true,"selected":true,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_6","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_6"},"a_attr":{"href":"#","id":"j1_6_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_2","type":"default"},{"id":"j1_3","text":"2FL\\Second Floor","icon":true,"li_attr":{"id":"j1_3"},"a_attr":{"href":"#","id":"j1_3_anchor"},"state":{"loaded":true,"opened":true,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_5","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_5"},"a_attr":{"href":"#","id":"j1_5_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_3","type":"default"},{"id":"j1_4","text":"Attic","icon":true,"li_attr":{"id":"j1_4"},"a_attr":{"href":"#","id":"j1_4_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"}]';
    }


    #[Route(path: '/tree-json.{_format}', name: 'app_tree_json', options: ['expose' => true])]
    public function treeJson(Request $request, $_format='html')
    {
        $data = array_map(function($name) { return ['text' =>  $name];}, ['Basement', 'First Floor', 'Second Floor', 'Attic']);
        return $this->jsonResponse($data, $request);
    }

}
