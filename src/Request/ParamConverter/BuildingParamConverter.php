<?php

namespace App\Request\ParamConverter;

use App\Entity\Building;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BuildingParamConverter implements ParamConverterInterface
{

    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
        return Building::class == $configuration->getClass();
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     * @throws Exception
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $params = $request->attributes->get('_route_params');

//        if (isset($params['buildingId']) && ($buildingId = $request->attributes->get('buildingId')))

        $buildingId = $request->attributes->get('buildingId');
        if ($buildingId === 'undefined') {
            throw new Exception("Invalid buildingId " . $buildingId);
        }

        // Check, if route attributes exists
        if (null === $buildingId ) {
            if (!isset($params['buildingId'])) {
                return; // no buildingId in the route, so leave.  Could throw an exception.
            }
        }

        // Get actual entity manager for class.  We can also pass it in, but that won't work for the doctrine tree extension.
        $em = $this->registry->getManagerForClass($configuration->getClass());
        $repository = $em->getRepository($configuration->getClass());

        // Try to find building by its Id
        $building = $repository->findOneBy(['code' => $buildingId]);

        if (null === $building || !($building instanceof Building)) {
            throw new NotFoundHttpException(sprintf('%s %s object not found.', $buildingId, $configuration->getClass()));
        }

        // Map found building to the route's parameter
        $request->attributes->set($configuration->getName(), $building);
    }

}
