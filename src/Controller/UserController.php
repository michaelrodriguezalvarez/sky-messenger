<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PerfilRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/api/get_logued_users", name="get_logued_users", methods={"GET"})
     */
    public function getLoguedList(Request $request, UserRepository $userRepository, PerfilRepository $perfilRepository): JsonResponse
    {
        try {
            $perfiles = $perfilRepository->findAll();               
            $users = [];
            // Estableciendo como primer elemento a la sala pública
            array_push($users, ['id'=>-1, 'nick'=>'Sala publica', 'perfil'=>-1]);

            foreach ($perfiles as $perfil) {
                //Aquí añadí 'roles'=>$perfil->getUsuario()->getRoles() para que devuevla el rol como parte de los datos.
                array_push($users, ['id'=>$perfil->getUsuario()->getId(), 'nick'=>$perfil->getNick(), 'perfil'=>$perfil->getId(), 'roles'=>$perfil->getUsuario()->getRoles()]);
            }

            $response = new JsonResponse();
            $response->setData([
                'success' => true,
                'data' => $users
            ]);
            $response->setStatusCode(Response::HTTP_OK);

            return $response;
        } catch (\Throwable $th) {           
            $response = new JsonResponse();
            $response->setData([
                'success' => false,
                'error' => $th->getMessage()
            ]);

            $response->setStatusCode(Response::HTTP_OK);

            return $response;
        }
    }
}
