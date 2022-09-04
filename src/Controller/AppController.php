<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\File;
use App\Entity\Location;
use App\Entity\Topic;
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
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    use JsonResponseTrait;
    private FileRepository $fileRepository;
    private TopicRepository $topicRepository;

    public function __construct(private EntityManagerInterface $entityManager )
    {
        $this->fileRepository = $this->entityManager->getRepository(File::class);
        $this->topicRepository = $this->entityManager->getRepository(Topic::class);
    }

    #[Route(path: '/basic-ajax/{buildingId}', name: 'app_basic_ajax')]
    public function basic_ajax(Building $building)
    {
        return $this->render('app/basic-ajax.html.twig', [
            'building' => $building
        ]);
    }

    #[Route(path: '/load-topics', name: 'app_load_topics')]
    public function loadTopics(Request $request, TopicsService $topicsService)
    {
            $topicsService->importTopics();
            return $this->redirectToRoute('app_tree', ['entity' => 'topics']);
    }

    #[Route(path: '/load-files', name: 'app_load_files')]
    public function loadFiles(Request $request, AppService $appService, ParameterBagInterface $bag)
    {
        $directory = $bag->get('kernel.project_dir');
        $appService->importDirectory($directory);
        return $this->redirectToRoute('app_tree', ['entity' => 'files']);
    }

    #[Route(path: '/knp-menu', name: 'app_knp_menu')]
    public function knpMenu(Request $request, AppService $appService, ParameterBagInterface $bag)
    {
        return $this->render('menu.html.twig');
    }

    #[Route(path: '/basic-{entity}', name: 'app_tree')]
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

    #[Route(path: '/html-demos', name: 'app_basic_html')]
    public function html(TopicRepository $topicRepository)
    {
        $count = $topicRepository->count([]);
        return $this->render('app/basic-html.html.twig', []);
    }

    #[Route(path: '/', name: 'app_homepage')]
    public function home()
    {
        return $this->render('app/home.html.twig', []);
    }

    private function getSampleJson() {
        return file_get_contents(__DIR__ . '/../../public/alt-format.json');
        return file_get_contents(__DIR__ . '/../../public/tree.json');
        return '[{"id":"j1_1","text":"Basement","icon":true,"li_attr":{"id":"j1_1"},"a_attr":{"href":"#","id":"j1_1_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_2","text":"1FL\\First Floor","icon":true,"li_attr":{"id":"j1_2"},"a_attr":{"href":"#","id":"j1_2_anchor"},"state":{"loaded":true,"opened":true,"selected":true,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_6","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_6"},"a_attr":{"href":"#","id":"j1_6_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_2","type":"default"},{"id":"j1_3","text":"2FL\\Second Floor","icon":true,"li_attr":{"id":"j1_3"},"a_attr":{"href":"#","id":"j1_3_anchor"},"state":{"loaded":true,"opened":true,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_5","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_5"},"a_attr":{"href":"#","id":"j1_5_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_3","type":"default"},{"id":"j1_4","text":"Attic","icon":true,"li_attr":{"id":"j1_4"},"a_attr":{"href":"#","id":"j1_4_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"}]';
    }


    #[Route(path: '/tree-json.{_format}', name: 'app_tree_json')]
    public function treeJson(Request $request, $_format='html')
    {
        $data = array_map(function($name) { return ['text' =>  $name];}, ['Basement', 'First Floor', 'Second Floor', 'Attic']);
        return $this->jsonResponse($data, $request);
    }

    #[Route(path: '/fetch.{_format}', name: 'app_tree_fetch')]
    public function fetch(Request $request, EntityManagerInterface $em, $_format='json')
    {
        $repository = $em->getRepository(Location::class);
        /** @var Location $location */
        $data = [];
        foreach ($repository->findAll() as $location) {
            array_push($data, [
                'id' => $location->getCode(),
                'data' => ['databaseId' => $location->getId()],
                'text' => $location->getName(),
                'parent' => $location->getParent() ? $location->getParent()->getCode() : '#'
            ]);
        }
        return new JsonResponse($data);
    }

    #[Route(path: '/save.{_format}', name: 'app_tree_save')]
    public function save(Request $request, EntityManagerInterface $em, $_format='html')
    {
        $repo = $em->getRepository(Location::class);
        $data = $request->get('json');
        // create nodes that don't exist.  Codes, though, are locked.
        foreach ($data as $node) {
            $node = (object)$node;
            if (!$location = $repo->findOneBy(['code' => $node->id])) {
                $location = (new Location());
                $em->persist($location);
            }
            $location->setName($node->text);
            $location->setParent($node->parent === '#' ? null : $repo->findOneBy(['code' => $node->parent]));
        }
        $em->flush();
        $data = ['status' => 'ok'];
        return $this->jsonResponse($data, $request);
    }


}
