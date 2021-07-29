<?php

namespace App\Controller;

use App\Entity\Perfil;
use App\Form\PerfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Flex\Path;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Repository\UserRepository;
use App\Repository\PerfilRepository;
use Doctrine\ORM\EntityManagerInterface;

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

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $Path = $this->getParameter('uploads_directory') . "/" . $perfil->getAvatar();
                if ($perfil->getAvatar() != null) {
                    if (file_exists($Path)) {
                        unlink($Path);
                    }
                }
                $perfil->setAvatar($newFilename);
            }


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('perfil_show', array('id' => $perfil->getId()));
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
        if ($this->isCsrfTokenValid('delete' . $perfil->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($perfil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('perfil_index');
    }

    /**
     * @Route("/api/delete_message", name="mensaje_eliminar_api", methods={"POST"})
     */
    public function delete_message(Request $request, UserRepository $userRepository, PerfilRepository $perfilRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($_POST) {
                $current_user = $this->getUser();
                if ($this->isCsrfTokenValid('delete_message' . $current_user->getId(), $request->request->get('_token'))) {
                    $id_mensaje_a_eliminar = $request->request->get('id_mensaje');
                    $perfil = $perfilRepository->findOneBy(array('usuario' => $current_user->getId()));
                    $mensajes_eliminados_previamente = $perfil->getMensajesEliminados();
                    if ($mensajes_eliminados_previamente == null) {
                        $mensajes_eliminados_previamente = [];
                        array_push($mensajes_eliminados_previamente, $id_mensaje_a_eliminar);
                        $perfil->setMensajesEliminados($mensajes_eliminados_previamente);
                        $entityManager->flush();
                    } else {
                        if (!in_array($id_mensaje_a_eliminar, $mensajes_eliminados_previamente)) {
                            array_push($mensajes_eliminados_previamente, $id_mensaje_a_eliminar);
                            $entityManager = $this->getDoctrine()->getManager();
                            $perfil->setMensajesEliminados($mensajes_eliminados_previamente);
                            $entityManager->flush();
                        }
                    }

                    $response = new JsonResponse();
                    $response->setData([
                        'success' => true,
                        'data' => 'Message ' + $id_mensaje_a_eliminar + ' deleted.',
                    ]);
                    $response->setStatusCode(Response::HTTP_OK);

                    return $response;
                } else {
                    throw new BadRequestException(Response::HTTP_BAD_REQUEST);
                }
            } else {
                throw new BadRequestException(Response::HTTP_BAD_REQUEST);
            }
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
