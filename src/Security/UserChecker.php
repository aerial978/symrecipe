<?php

namespace App\Security;

use App\Entity\User as AppUser;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatableInterface;

class UserChecker extends AbstractController implements UserCheckerInterface
{
    private FlashyNotifier $flashy;
    
    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }
    
    
    public function checkPreAuth(UserInterface $user): void
    {
        
    }
    
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
                
            return;
        }

        $this->flashy->success('You are connected !', 'http://your-awesome-link.com');

    }
}