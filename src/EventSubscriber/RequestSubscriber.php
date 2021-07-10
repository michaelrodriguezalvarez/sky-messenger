<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $tokenStorageInterface;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }
    
    public static function getSubscribedEvents(): array
    {
        return array(
            RequestEvent::class => 'onKernelRequest',
        );
    }

    public function onKernelRequest(RequestEvent $event){
        if (!$event->isMasterRequest()) {
            return;
        }

        if($this->tokenStorageInterface->getToken() != null){
            $this->tokenStorageInterface->getToken()->getUser();
            /**@var \Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken $token */
            $token = $this->tokenStorageInterface->getToken();
            /**@var User $user */
            $user = $token->getUser();
            if($user != "anon."){          
                if(!$user->isActiveNow()){
                    $user->setLastActivityAt(new \DateTime());
                    $this->entityManager->flush();
                }  
            }                     
        } 
    }
}