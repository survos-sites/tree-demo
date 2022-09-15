<?php


// uses Survos Param Converter, from the UniqueIdentifiers method of the entity.

namespace App\Controller;

use App\Entity\Topic;
use App\Form\TopicType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TopicRepository;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Survos\WorkflowBundle\Traits\HandleTransitionsTrait;

#[Route('/topic')]
class TopicCollectionController extends AbstractController
{

    use HandleTransitionsTrait;


    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    #[Route(path: '/topic-tree-html', name: 'app_tree_html')]
    public function topic_tree_html(Request $request, TopicRepository $repo)
    {
        return $this->render('topic/topic_tree.html.twig', [
            'entities' => $repo->findBy(['level' => 0]),
            'html' => ''// $htmlTree
        ]);
    }

    #[Route(path: '/topic-tree-api', name: 'topic_tree_api')]
    public function topic_tree_api(Request $request)
    {
        return $this->render('topic/topic_tree_api.html.twig', [
            'topicClass' => Topic::class,
            'html' => ''// $htmlTree
        ]);
    }

    #[Route(path: '/list/{marking}', name: 'topic_browse', methods: ['GET'])]
    public function browse(string $marking = Topic::PLACE_NEW): Response
    {
        $class = Topic::class;
// WorkflowInterface $projectStateMachine
        $markingData = []; // $this->workflowHelperService->getMarkingData($projectStateMachine, $class);

        return $this->render('topic/browse.html.twig', [
            'class' => $class,
            'marking' => $marking,
            'filter' => [],
//            'owner' => $owner,
        ]);
    }

    #[Route('/index', name: 'topic_index')]
    public function index(TopicRepository $topicRepository): Response
    {
        return $this->render('topic/index.html.twig', [
            'topics' => $topicRepository->findBy([], [], 30),
        ]);
    }

    #[Route('/overview', name: 'topic_overview')]
    public function overview(): Response
    {
        return $this->render('topic/overview.html.twig', [
        ]);
    }

    #[Route('topic/new', name: 'topic_new')]
    public function new(Request $request): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('topic_index');
        }

        return $this->render('topic/new.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }
}
