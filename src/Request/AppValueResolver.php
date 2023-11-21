<?php

namespace App\Request;

use App\Entity\Building;
use App\Entity\Lls\Transcript;
use App\Entity\Ost\Movie;
use App\Entity\Ost\Subtitle;
use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Survos\CoreBundle\Entity\RouteParametersInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AppValueResolver implements ValueResolverInterface
{
    public function __construct(
        protected LoggerInterface       $logger,
        protected EntityManagerInterface $entityManager
    ) {

    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        // get the argument type (e.g. BookingId)
        $argumentType = $argument->getType();
        if (! is_subclass_of($argumentType, RouteParametersInterface::class)) {
            return [];
        }
        $shortName = (new \ReflectionClass($argumentType))->getShortName();
        $idField = lcfirst($shortName) . 'Id'; // e.g. projectId
        if ($request->attributes->has($idField)) {
            $idFieldValue = $request->attributes->get($idField); // e.g. goitia
        } else {
            $idFieldValue = null;
        }

        $repository = $this->entityManager->getRepository($argumentType);
        $instance =  match ($argumentType) {
            Topic::class,
            Building::class => $repository->findOneBy(['code' => $idFieldValue]),
            default => null
        };
        assert($instance, "Missing $argumentType");
        $request->attributes->set($argument->getName(), $instance);
        return [$instance];
    }

}
