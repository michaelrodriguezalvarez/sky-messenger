<?php

namespace App\Controller;

use App\Repository\PerfilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    /**
     * @Route("/chat", name="chat")
     */
    public function index(PerfilRepository $perfilRepository): Response
    {
        $current_user = $this->getUser();
        $perfil = $perfilRepository->findOneBy(array('usuario' => $current_user->getId()));
        $avatar = $perfil->getAvatar();

        return $this->render('chat/index.html.twig', [
            'controller_name' => 'ChatController',
            'perfil' => $perfil,
            'avatar' => $avatar,
        ]);
    }

    /**
     * @Route("/", name="app_landing_page")
     */
    public function landing_page(Request $request): Response
    {
        return $this->render('chat/landing_page.html.twig');
    }
}
