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
        $buildingMenu = $this->addMenuItem($menu, ['menu_code' => 'building_dropdown', 'label' => 'Buildings']);
        foreach (['index', 'new'] as $routeSuffix) {
            $this->addMenuItem($buildingMenu, ['route' => 'building_' . $routeSuffix]);
        }
        foreach ($this->buildingRepository->findAll() as $building) {
            $this->addMenuItem($buildingMenu, ['route' => 'app_basic_ajax',
                'label' => $building,
                'rp'=> $building]);
        }

        $this->addMenuItem($menu, ['route' => 'app_basic_html', 'icon' => 'fas fa-home']);
        $this->addMenuItem($menu, ['route' => 'topic_index', 'icon' => 'fas fa-home']);

        foreach (['files', 'topics'] as $entityName) {
            $this->addMenuItem($menu, ['label' => $entityName, 'route' => 'app_tree', 'rp' => ['entity' => $entityName]]);
        }


        $adminMenu = $this->addMenuItem($menu, ['menu_code' => 'admin_dropdown']);
//        $this->addMenuItem($adminMenu, ['route' => 'easyadmin']);
        $this->addMenuItem($adminMenu, ['route' => 'api_entrypoint']);
        $this->addMenuItem($adminMenu, ['route' => 'api_doc']);

        $this->addMenuItem($menu, ['route' => 'app_homepage', 'label' => "Home", 'icon' => 'fas fa-home']);
    }

}
