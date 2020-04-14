<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\LandingBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends BaseController
{
    // see https://www.jstree.com/docs/json/
    // and maybe https://www.phpflow.com/demo/dynamic-jstree-php-mysql-demo/#

    private function getRepo(): LocationRepository
    {
        return $this->getDoctrine()->getRepository(Location::class);
    }

    public function __construct()
    {
    }

    /**
     * @Route("/basic-ajax", name="app_basic_ajax")
     */
    public function index()
    {
        return $this->render('app/basic-ajax.html.twig', [
        ]);
    }

    /**
     * @Route("/html-demos", name="app_basic_html")
     */
    public function html()
    {
        return $this->render('app/basic-html.html.twig', [
        ]);
    }

    private function getSampleJson() {
        return file_get_contents(__DIR__ . '/../../public/alt-format.json');
        return file_get_contents(__DIR__ . '/../../public/tree.json');
        return '[{"id":"j1_1","text":"Basement","icon":true,"li_attr":{"id":"j1_1"},"a_attr":{"href":"#","id":"j1_1_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_2","text":"1FL\\First Floor","icon":true,"li_attr":{"id":"j1_2"},"a_attr":{"href":"#","id":"j1_2_anchor"},"state":{"loaded":true,"opened":true,"selected":true,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_6","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_6"},"a_attr":{"href":"#","id":"j1_6_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_2","type":"default"},{"id":"j1_3","text":"2FL\\Second Floor","icon":true,"li_attr":{"id":"j1_3"},"a_attr":{"href":"#","id":"j1_3_anchor"},"state":{"loaded":true,"opened":true,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"},{"id":"j1_5","text":"1BR: Bedroom 1","icon":true,"li_attr":{"id":"j1_5"},"a_attr":{"href":"#","id":"j1_5_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"j1_3","type":"default"},{"id":"j1_4","text":"Attic","icon":true,"li_attr":{"id":"j1_4"},"a_attr":{"href":"#","id":"j1_4_anchor"},"state":{"loaded":true,"opened":false,"selected":false,"disabled":false},"data":{},"parent":"#","type":"default"}]';
    }


    /**
     * @Route("/tree-json.{_format}", name="app_tree_json")
     */
    public function treeJson(Request $request, $_format='html')
    {

        $data = array_map(function($name) { return ['text' =>  $name];}, ['Basement', 'First Floor', 'Second Floor', 'Attic']);
        return $this->jsonResponse($data, $request);
    }

    /**
     * @Route("/fetch.{_format}", name="app_tree_fetch")
     */
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

    /**
     * @Route("/save.{_format}", name="app_tree_save")
     */
    public function save(Request $request, $_format='html')
    {
        $em = $this->getDoctrine()->getManager();
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
