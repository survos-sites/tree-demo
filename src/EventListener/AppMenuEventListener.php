<?php

declare(strict_types=1);

namespace App\EventListener;

use Survos\TablerBundle\Event\MenuEvent;
use Survos\TablerBundle\Menu\MenuBuilderTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class AppMenuEventListener
{
    use MenuBuilderTrait;

    public function __construct(
        #[Autowire('%kernel.debug%')] private readonly bool $debug,
        private readonly Security $security,
    ) {}

    #[AsEventListener(event: MenuEvent::NAVBAR_MENU)]
    public function navigation(MenuEvent $event): void
    {
        $this->add($event->menu, 'app_homepage', label: 'Overview', icon: 'tabler:home', translationDomain: 'routing');
        $this->add($event->menu, 'topics_playground', label: 'Table playground', icon: 'tabler:table', translationDomain: 'routing');
        $this->add($event->menu, 'app_tree_html', label: 'Topic tree', icon: 'tabler:hierarchy', translationDomain: 'routing');
        $this->add($event->menu, 'topic_index', label: 'API grid', icon: 'tabler:layout-grid', translationDomain: 'routing');
        $more = $this->addSubmenu($event->menu, label: 'More demos', icon: 'tabler:dots', translationDomain: 'routing');
        $this->add($more, 'topic_tree_api', label: 'API tree', translationDomain: 'routing');
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $this->add($more, 'app_repo_files', label: 'File browser', translationDomain: 'routing');
        }
        $this->add($more, 'app_inventory', label: 'My inventory', translationDomain: 'routing');
        $this->add($more, 'app_twig_browser_demo', label: 'Twig browser', translationDomain: 'routing');
    }
}
