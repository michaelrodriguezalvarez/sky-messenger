<?php

namespace App\Controller;

use App\Entity\Perfil;
use App\Form\PerfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/perfil")
 */
class PerfilController extends AbstractController
{
    /**
     * @Route("/", name="perfil_index", methods={"GET"})
     */
    public function index(): Response
    {
        $perfils = $this->getDoctrine()
            ->getRepository(Perfil::class)
            ->findAll();

        return $this->render('perfil/index.html.twig', [
            'perfils' => $perfils,
        ]);
    }

    /**
     * @Route("/new", name="perfil_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $perfil = new Perfil();
        $form = $this->createForm(PerfilType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($perfil);
            $entityManager->flush();

            return $this->redirectToRoute('perfil_index');
        }

        return $this->render('perfil/new.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="perfil_show", methods={"GET"})
     */
    public function show(Perfil $perfil): Response
    {
        $current_user = $this->getUser();
        return $this->render('perfil/show.html.twig', [
            'perfil' => $perfil,
            'current_user_id' => $current_user->getId(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="perfil_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Perfil $perfil): Response
    {
        /** @var $current_user User */
        $current_user = $this->getUser();
        if($current_user->getRoles()[0] == "ROLE_USER"){
            if($current_user->getId() != $perfil->getUsuario()->getId()){
                throw $this->createAccessDeniedException();
            }
        }
        $form = $this->createForm(PerfilType::class, $perfil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('perfil_index');
        }

        return $this->render('perfil/edit.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="perfil_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Perfil $perfil): Response
    {
        if ($this->isCsrfTokenValid('delete'.$perfil->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($perfil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('perfil_index');
    }
}
