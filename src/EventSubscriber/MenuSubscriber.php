<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use KevinPapst\AdminLTEBundle\Event\KnpMenuEvent;
use Survos\LandingBundle\Traits\KnpMenuHelperTrait;

class MenuSubscriber implements EventSubscriberInterface
{
    use KnpMenuHelperTrait;

    public function onKnpMenuEvent(KnpMenuEvent $event)
    {

        $menu = $event->getMenu();

        $buildingMenu = $this->addMenuItem($menu, ['menu_code' => 'building_dropdown', 'label' => 'Buildings']);
        foreach (['index', 'new'] as $routeSuffix) {
            $this->addMenuItem($buildingMenu, ['route' => 'building_' . $routeSuffix]);
        }

        $menu->addChild('survos_landing', ['label' => 'home', 'route' => 'app_homepage'])->setAttribute('icon', 'fas fa-home');
        $menu->addChild('app_basic_ajax', ['route' => 'app_basic_ajax']);
        $menu->addChild('app_basic_html', ['route' => 'app_basic_html']);

        $menu->addChild('survos_landing', ['route' => 'app_homepage'])->setAttribute('icon', 'fas fa-home');
        $menu->addChild('easyadmin', ['route' => 'easyadmin'])->setAttribute('icon', 'fas fa-database');

        // ...
    }

    public static function getSubscribedEvents()
    {
        return [
            KnpMenuEvent::class => 'onKnpMenuEvent',
        ];
    }
}
