<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Location;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/** @implements ProcessorInterface<Location, Location|void> */
final class InventoryProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persist,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private readonly ProcessorInterface $remove,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->security->getUser();
        if (!$user instanceof User || !$data instanceof Location) {
            throw new AccessDeniedHttpException();
        }
        $previous = $context['previous_data'] ?? null;
        if ($data->getId() !== null) {
            if ($data->getBuilding()?->getUser()?->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException();
            }
            // The user's root is permanent, even if PATCH submitted a new parent.
            if (($previous instanceof Location ? $previous : $data)->getParent() === null) {
                throw new UnprocessableEntityHttpException('Your inventory root cannot be changed or deleted. Add items beneath it.');
            }
        }
        if ($operation instanceof DeleteOperationInterface) {
            return $this->remove->process($data, $operation, $uriVariables, $context);
        }
        $parent = $data->getParent();
        if (!$parent || $parent->getBuilding()?->getUser()?->getId() !== $user->getId()) {
            throw new UnprocessableEntityHttpException('Choose a parent in your inventory.');
        }
        if ($data->getId() === null) {
            // Ownership comes from the authorized parent, never a submitted owner.
            $data->setBuilding($parent->getBuilding());
        } elseif ($data->getBuilding()?->getId() !== $parent->getBuilding()?->getId()) {
            throw new UnprocessableEntityHttpException('Items must remain in the same inventory.');
        }
        $seen = [];
        for ($ancestor = $parent; $ancestor; $ancestor = $ancestor->getParent()) {
            $id = $ancestor->getId();
            if ($ancestor === $data || ($data->getId() !== null && $id === $data->getId()) || isset($seen[$id])) {
                throw new UnprocessableEntityHttpException('An item cannot be moved beneath itself or its descendants.');
            }
            $seen[$id] = true;
        }
        return $this->persist->process($data, $operation, $uriVariables, $context);
    }
}
