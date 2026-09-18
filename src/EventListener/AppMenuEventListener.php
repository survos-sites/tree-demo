<?php

declare(strict_types=1);

namespace App\EventListener;

use Survos\TablerBundle\Event\MenuEvent;
use Survos\TablerBundle\Traits\KnpMenuHelperTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AppMenuEventListener
{
    use KnpMenuHelperTrait;

    public function __construct(
        #[Autowire('%kernel.debug%')] private readonly bool $debug,
        private readonly Security $security,
    ) {}

    #[AsEventListener(event: MenuEvent::NAVBAR_MENU)]
    public function navigation(MenuEvent $event): void
    {
        $this->add($event->menu, 'app_homepage', label: 'Overview', icon: 'tabler:home');
        $this->add($event->menu, 'topics_playground', label: 'Table playground', icon: 'tabler:table');
        $this->add($event->menu, 'app_tree_html', label: 'Topic tree', icon: 'tabler:hierarchy');
        $this->add($event->menu, 'topic_index', label: 'API grid', icon: 'tabler:layout-grid');
        $more = $this->addSubmenu($event->menu, label: 'More demos', icon: 'tabler:dots');
        $this->add($more, 'topic_tree_api', label: 'API tree');
        if ($this->debug || $this->security->isGranted('ROLE_ADMIN')) {
            $this->add($more, 'app_repo_files', label: 'File browser');
        }
        $this->add($more, 'building_index', label: 'Inventory');
        $this->add($more, 'app_twig_browser_demo', label: 'Twig browser');
    }
}
