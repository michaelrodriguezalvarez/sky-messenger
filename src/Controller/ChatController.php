<?php

namespace App\Controller;

use App\Repository\PerfilRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $perfil = $perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));
        return $this->render('chat/index.html.twig', [
            'controller_name' => 'ChatController',
            'perfil' => $perfil,
        ]);
    }

    /**
     * @Route("/api/get_logued_users", name="get_logued_users")
     */
    public function getLoguedList(Request $request, UserRepository $userRepository): Response
    {
        $users_logued = $userRepository->findAll();
        $users = [];

        foreach ($users_logued as $user) {
            array_push($users, ['id'=>$user->getId(), 'email'=>$user->getEmail()]);
        }

        $response = new JsonResponse();
        $response->setData([
            'success' => true,
            'data' => $users
        ]);
        return $response;
    }
}
