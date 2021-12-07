<?php

namespace App\EventSubscriber;

use App\Repository\BuildingRepository;
use Survos\BaseBundle\Menu\BaseMenuSubscriber;
use Survos\BaseBundle\Menu\MenuBuilder;
use Survos\BaseBundle\Traits\KnpMenuHelperTrait;
use Survos\BaseBundle\Event\KnpMenuEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class MenuSubscriber extends BaseMenuSubscriber implements EventSubscriberInterface
{
    // use KnpMenuHelperTrait; // for auth menu

    private $requestStack;
    private $authorizationChecker;
    private $security;
    /**
     * @var BuildingRepository
     */
    private BuildingRepository $buildingRepository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker,
                                BuildingRepository $buildingRepository,
                                Security $security, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
        $this->buildingRepository = $buildingRepository;
    }

   // use KnpMenuHelperTrait;

    public function onKnpMenuEvent(KnpMenuEvent $event)
    {

        $menu = $event->getMenu();

        $buildingMenu = $this->addMenuItem($menu, ['menu_code' => 'building_dropdown', 'label' => 'Buildings']);
        foreach (['index', 'new'] as $routeSuffix) {
            $this->addMenuItem($buildingMenu, ['route' => 'building_' . $routeSuffix]);
        }
        foreach ($this->buildingRepository->findAll() as $building) {
            $this->addMenuItem($buildingMenu, ['route' => 'app_basic_ajax',
                'label' => $building,
                'rp'=> $building]);
        }


        $this->addMenuItem($menu, ['route' => 'app_homepage', 'icon' => 'fas fa-home']);

        foreach (['files', 'topics'] as $entityName) {
            $this->addMenuItem($menu, ['label' => $entityName, 'route' => 'app_tree', 'rp' => ['entity' => $entityName]]);
        }

        $menu->addChild('app_basic_html', ['route' => 'app_basic_html']);

        $adminMenu = $this->addMenuItem($menu, ['menu_code' => 'admin_dropdown']);
        $this->addMenuItem($adminMenu, ['route' => 'easyadmin']);
        $this->addMenuItem($adminMenu, ['route' => 'api_entrypoint']);
        $this->addMenuItem($adminMenu, ['route' => 'api_doc']);

        // ...
    }

    public static function getSubscribedEvents()
    {
        return [
            MenuBuilder::SIDEBAR_MENU_EVENT => 'onKnpMenuEvent',
        ];
    }
}
