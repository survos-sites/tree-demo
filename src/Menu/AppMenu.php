<?php

namespace App\Menu;

use App\Entity\Bill;
use App\Entity\Scorecard;
use App\Repository\BillRepository;
use App\Repository\BuildingRepository;
use App\Repository\JurisdictionRepository;
use Survos\BaseBundle\Menu\AdminMenuTrait;
use Survos\WorkflowBundle\Service\WorkflowHelperService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Umbrella\AdminBundle\Menu\BaseAdminMenu;
use Umbrella\AdminBundle\UmbrellaAdminConfiguration;
use Umbrella\CoreBundle\Menu\Builder\MenuBuilder;
use Umbrella\CoreBundle\Menu\Builder\MenuItemBuilder;
use Umbrella\CoreBundle\Menu\DTO\MenuItem;
use function Symfony\Component\String\u;

class AppMenu extends BaseAdminMenu
{
    use AdminMenuTrait;

    public function __construct(private AuthorizationCheckerInterface $security,
                                protected Environment $twig,
                                protected UmbrellaAdminConfiguration $configuration,
                                private BuildingRepository $buildingRepository,
                                RequestStack $requestStack)
    {
        parent::__construct($this->twig, $configuration);
    }


    public function buildMenu(MenuBuilder $builder, array $options)
    {
        $menu = $builder->root();

    }

}
