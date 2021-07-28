<?php

namespace App\Controller;

use App\Entity\Perfil;
use App\Form\PerfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Flex\Path;

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
    public function edit(Request $request, Perfil $perfil, SluggerInterface $slugger): Response
    {
        /** @var $current_user User */
        $current_user = $this->getUser();
        if ($current_user->getRoles()[0] == "ROLE_USER") {
            if ($current_user->getId() != $perfil->getUsuario()->getId()) {
                throw $this->createAccessDeniedException();
            }
        }
        $form = $this->createForm(PerfilType::class, $perfil);
        $form->handleRequest($request);

        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $avatarFile = $form->get('avatar')->getData();

                if ($avatarFile) {
                    $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);               
                    $safeFilename = $slugger->slug($originalFilename);               
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();
                    
                        $avatarFile->move(
                            $this->getParameter('uploads_directory'),
                            $newFilename
                        );

                        $path = $this->getParameter('uploads_directory') . "/" . $perfil->getAvatar();
                        if ($perfil->getAvatar() != null){
                            if (file_exists($path)){
                                unlink($path);
                            }
                        }
                        $perfil->setAvatar($newFilename);
                }

                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Perfil guardado satisfactoriamente.');

                return $this->redirectToRoute('perfil_show', array('id'=>$perfil->getId()));
            } catch (FileException $fe) {                    
                $form->get('avatar')->addError(new FormError('No se puedo almacenar la imagen en el servidor. Intente más tarde.'));
                $errors = $form->getErrors(true);
            }
        }

        return $this->render('perfil/edit.html.twig', [
            'perfil' => $perfil,
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/{id}", name="perfil_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Perfil $perfil): Response
    {
        if ($this->isCsrfTokenValid('delete' . $perfil->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($perfil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('perfil_index');
    }
}
