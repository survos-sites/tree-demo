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

#[Route('/topic/{topicId}')]
class TopicController extends AbstractController
{

public function __construct(private EntityManagerInterface $entityManager) {

}

#[Route(path: '/transition/{transition}', name: 'topic_transition')]
public function transition(Request $request, WorkflowInterface $topicStateMachine, string $transition, Topic $topic): Response
{
if ($transition === '_') {
$transition = $request->request->get('transition'); // the _ is a hack to display the form, @todo: cleanup
}

$this->handleTransitionButtons($repoStateMachine, $transition, $github);
$this->entityManager->flush(); // to save the marking
return $this->redirectToRoute('topic_show', $github->getRP());
}

#[Route('/', name: 'topic_show', options: ['expose' => true])]
    public function show(Topic $topic): Response
    {
        return $this->render('topic/show.html.twig', [
            'topic' => $topic,
        ]);
    }

#[Route('/edit', name: 'topic_edit', options: ['expose' => true])]
    public function edit(Request $request, Topic $topic): Response
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('topic_index');
        }

        return $this->render('topic/edit.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }

#[Route('/delete', name: 'topic_delete', methods:['DELETE'])]
    public function delete(Request $request, Topic $topic): Response
    {
        // hard-coded to getId, should be get parameter of uniqueIdentifiers()
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($topic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('topic_index');
    }
}
