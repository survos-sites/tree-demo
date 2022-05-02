<?php
declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\Topic;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class TopicParamConverter implements ParamConverterInterface
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration): bool
    {
        return Topic::class == $configuration->getClass();
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
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $params = $request->attributes->get('_route_params');

//        if (isset($params['topicId']) && ($topicId = $request->attributes->get('topicId')))

        $topicId = $request->attributes->get('topicId');
        if ($topicId === 'undefined') {
            throw new Exception("Invalid topicId " . $topicId);
        }

        // Check, if route attributes exists
        if (null === $topicId ) {
            if (!isset($params['topicId'])) {
                return false; // no topicId in the route, so leave.  Could throw an exception.
            }
        }

        // Get actual entity manager for class.  We can also pass it in, but that won't work for the doctrine tree extension.
        $repository = $this->registry->getManagerForClass($configuration->getClass())?->getRepository($configuration->getClass());

        // Try to find the entity
        if (!$topic = $repository->findOneBy(['code' => $topicId])) {
            throw new NotFoundHttpException(sprintf('%s %s object not found.', $topicId, $configuration->getClass()));
        }

        // Map found topic to the route's parameter
        $request->attributes->set($configuration->getName(), $topic);
        return true;
    }

}
