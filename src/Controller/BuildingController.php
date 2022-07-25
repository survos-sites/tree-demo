<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\Location;
use App\Form\BuildingType;
use App\Repository\BuildingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/building')]
class BuildingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {

    }
    #[Route(path: '/browse', name: 'building_index', methods: ['GET'])]
    public function index(BuildingRepository $buildingRepository) : Response
    {
        return $this->render('building/index.html.twig', [
            'buildings' => $buildingRepository->findAll(),
        ]);
    }
    #[Route(path: '/new', name: 'building_new', methods: ['GET', 'POST'])]
    public function new(Request $request) : Response
    {
        $building = new Building();
        $form = $this->createForm(BuildingType::class, $building);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($building);
            $entityManager->flush();

            return $this->redirectToRoute('building_index');
        }
        return $this->renderForm('building/new.html.twig', [
            'building' => $building,
            'form' => $form,
        ]);
    }
    #[Route(path: '/show/{buildingId}', name: 'building_show', methods: ['GET'])]
    public function show(Building $building, EntityManagerInterface $entityManager) : Response
    {
        $repo = $entityManager->getRepository(Location::class);
        return $this->render('building/show.html.twig', [
            'filter' => [
                'building' => '/api/buildings/' . $building->getId()
            ],
//            'tree' => $repo->childrenHierarchy( $repo->findOneBy(['name' => $building->getName()]), true,
//                ['html' => true, 'decorate' => true]  ),
            'building' => $building,
        ]);
    }
    #[Route(path: '/{buildingId}/edit', name: 'building_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Building $building) : Response
    {
        $form = $this->createForm(BuildingType::class, $building);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('building_index');
        }
        return $this->render('building/edit.html.twig', [
            'building' => $building,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/{buildingId}', name: 'building_delete', methods: ['DELETE'])]
    public function delete(Request $request, Building $building) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$building->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($building);
            $entityManager->flush();
        }
        return $this->redirectToRoute('building_index');
    }
}
