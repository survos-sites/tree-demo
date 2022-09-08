<?php

namespace App\EventListener;

use App\Repository\BuildingRepository;
use Survos\BootstrapBundle\Event\KnpMenuEvent;
use Survos\BootstrapBundle\Traits\KnpMenuHelperInterface;
use Survos\BootstrapBundle\Traits\KnpMenuHelperTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsEventListener(event: KnpMenuEvent::SIDEBAR_MENU_EVENT, method: 'appSidebarMenu')]
#[AsEventListener(event: KnpMenuEvent::FOOTER_MENU_EVENT, method: 'footerMenu')]
final class AppMenuEventListener implements KnpMenuHelperInterface
{
    use KnpMenuHelperTrait;

    public function __construct(
        private BuildingRepository $buildingRepository,
        private ?AuthorizationCheckerInterface $security=null)
    {

        $this->setAuthorizationChecker($this->security);
    }

    public function footerMenu(KnpMenuEvent $event): void
    {
        [$menu, $options] = [$event->getMenu(), $event->getOptions()];
    }

    public function appSidebarMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $this->addMenuItem($menu, ['route' => 'app_homepage', 'label' => "Home", 'icon' => 'fas fa-home']);

        $this->addHeading($menu, 'Inventory Demo');
        $this->add($menu, 'building_index', label: 'List');
        if ($this->isGranted('ROLE_USER')) {
            $this->add($menu, 'building_new', label: 'Create New Building');
        }

        $subMenu = $this->addSubmenu($menu, "Buildings");
        foreach ($this->buildingRepository->findAll() as $building) {
            $this->add($subMenu, 'building_show', $building, $building->getName());
        }


        $this->addMenuItem($menu, ['route' => 'app_basic_html', 'icon' => 'fas fa-home']);

        $this->addHeading($menu, label: "Topics");
        $this->add($menu, 'topic_index', label: "Topics Table", icon: "fas fa-tree");

        $this->addMenuItem($menu, ['route' => 'topic_index', 'label' => 'Topics Grid', 'icon' => 'fas fa-home']);
        $this->addMenuItem($menu, ['label' => 'Topic Tree HTML', 'route' => 'app_tree_html']);
        $this->addMenuItem($menu, ['label' => 'Topic Tree API', 'route' => 'app_tree_api']);

        $this->addHeading($menu, label: "File Browser");
        foreach (['files'] as $entityName) {
            $this->addMenuItem($menu, ['label' => $entityName, 'route' => 'app_tree', 'rp' => ['entity' => $entityName]]);
        }


        $this->addHeading($menu, label: "API");

        $this->add($menu, 'api_entrypoint', external: true);
        $this->add($menu, 'api_doc', external: true);

        $this->addMenuItem($menu, ['label' => 'Auth', 'style' => 'heading']);
        $this->authMenu($this->security, $menu);
    }

}
