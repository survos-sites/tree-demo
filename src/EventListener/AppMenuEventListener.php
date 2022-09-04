<?php

namespace App\EventListener;

use App\Repository\BuildingRepository;
use Survos\BootstrapBundle\Event\KnpMenuEvent;
use Survos\BootstrapBundle\Traits\KnpMenuHelperTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsEventListener(event: KnpMenuEvent::SIDEBAR_MENU_EVENT, method: 'appSidebarMenu')]
#[AsEventListener(event: KnpMenuEvent::SIDEBAR_MENU_EVENT, method: 'coreMenu')]
#[AsEventListener(event: KnpMenuEvent::PAGE_MENU_EVENT, method: 'coreMenu')]
final class AppMenuEventListener
{
    use KnpMenuHelperTrait;

    public function __construct(
        private BuildingRepository $buildingRepository,
        private ?AuthorizationCheckerInterface $security=null)
    {

        $this->setAuthorizationChecker($this->security);
    }

    public function coreMenu(KnpMenuEvent $event): void
    {
        [$menu, $options] = [$event->getMenu(), $event->getOptions()];
    }

    public function supports(KnpMenuEvent $event): bool
    {
        $this->options = (new OptionsResolver())
            ->setDefaults([
                'project' => null,
                'projectCore' => null,
                'subMenu' => null,
                'showAppMenu' => true,
            ])->resolve($event->getOptions());

        // if there's no project, ignore this menu.
        return $this->options['showAppMenu']; // !(bool)$this->options['projectCore'];
    }

    public function appSidebarMenu(KnpMenuEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $menu = $event->getMenu();

        $buildingMenu = $this->addMenuItem($menu, ['menu_code' => 'building_dropdown', 'label' => 'Buildings']);
        foreach (['index', 'new'] as $routeSuffix) {
            $this->addMenuItem($buildingMenu, ['route' => 'building_' . $routeSuffix]);
        }
        foreach ($this->buildingRepository->findAll() as $building) {
            $this->addMenuItem($buildingMenu, ['route' => 'building_show',
                'label' => $building,
                'rp'=> $building]);
        }

        $this->addMenuItem($menu, ['route' => 'app_basic_html', 'icon' => 'fas fa-home']);
        $this->addMenuItem($menu, ['route' => 'topic_index', 'label' => 'Topics Grid', 'icon' => 'fas fa-home']);
        $this->addMenuItem($menu, ['label' => 'Topic Tree HTML', 'route' => 'app_tree_html']);
        $this->addMenuItem($menu, ['label' => 'Topic Tree API', 'route' => 'app_tree_api']);

        foreach (['files'] as $entityName) {
            $this->addMenuItem($menu, ['label' => $entityName, 'route' => 'app_tree', 'rp' => ['entity' => $entityName]]);
        }


        $adminMenu = $this->addMenuItem($menu, ['menu_code' => 'admin_dropdown']);
//        $this->addMenuItem($adminMenu, ['route' => 'easyadmin']);
        $this->addMenuItem($adminMenu, ['route' => 'api_entrypoint']);
        $this->addMenuItem($adminMenu, ['route' => 'api_doc']);

        $this->addMenuItem($menu, ['route' => 'app_homepage', 'label' => "Home", 'icon' => 'fas fa-home']);
        [$menu, $options] = [$event->getMenu(), $event->getOptions()];
//        dd($options);


//        $projectMenu = $this->addMenuItem($menu, ['route' => 'project_index', 'label' => 'All projects']);
//        foreach ($this->entityManager->getRepository(Project::class)->findAll() as $project) {
//            $singleProjectMenu = $this->addMenuItem($projectMenu, ['route' => 'project_show', 'rp' => $project, 'label' => $project]);
//        }

        $this->addMenuItem($menu, ['route' => 'api_doc']);
//        $this->addMenuItem($menu, ['route' => 'lst_index']);

        // add the login/logout menu items.
        $this->addMenuItem($menu, ['label' => 'Auth', 'style' => 'heading']);
        $this->authMenu($this->security, $menu);
    }

}
