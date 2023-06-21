<?php

namespace App\EventListener;

use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutSubscriber extends AbstractController implements EventSubscriberInterface
{
    private FlashyNotifier $flashy;
    
    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(): void
    {
        $this->flashy->success('You are disconnected !', 'http://your-awesome-link.com');
    }
}