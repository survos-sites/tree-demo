<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Building;
use App\Repository\BuildingRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/building')]
#[IsGranted('ROLE_USER')]
final class BuildingController extends AbstractController
{
    #[Route('/browse', name: 'building_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_inventory');
    }

    #[Route('/show/{buildingId}', name: 'building_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['buildingId' => 'code'])] Building $building): Response
    {
        if ($building->getUser()?->getId() !== $this->getUser()?->getId()) {
            throw $this->createNotFoundException();
        }
        return $this->render('inventory/index.html.twig', ['building' => $building]);
    }
}
