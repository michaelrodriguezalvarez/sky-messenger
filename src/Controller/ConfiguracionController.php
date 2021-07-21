<?php

namespace App\Controller;

use App\Form\ConfiguracionType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ConfiguracionController extends AbstractController
{
    /**
     * @Route("/configuracion", name="configuracion", methods={"GET","POST"})
     */
    public function index(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $userRepository->find($this->getUser()->getId());
        $form = $this->createForm(ConfiguracionType::class, $user);
        $form->handleRequest($request);

        $errors = $form->getErrors(true);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            if($form->get('password')->getData() != null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
            }           

            $entityManager->flush();

            $this->addFlash('success', 'Su configuración fue guardada correctamente.');
            return $this->redirectToRoute('configuracion');
        }

        return $this->render('configuracion/index.html.twig', [
            'errors' => $errors,
            'user'=> $user,
            'form' => $form->createView(),
        ]);
    }
}
