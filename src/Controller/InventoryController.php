<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Inventory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'app_inventory', methods: ['GET'], options: ['expose' => true])]
    public function __invoke(#[CurrentUser] User $user, Inventory $inventory): Response
    {
        return $this->render('inventory/index.html.twig', ['building' => $inventory->forUser($user)]);
    }
}
