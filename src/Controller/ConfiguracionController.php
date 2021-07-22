<?php

namespace App\Controller;

use App\Entity\Configuracion;
use App\Form\ConfiguracionType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ConfiguracionController extends AbstractController
{
    /**
     * @Route("/configuracion", name="configuracion", methods={"GET","POST"})
     */
    public function index(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        /**@var User $user */
        $user = $userRepository->find($this->getUser()->getId());
        /**@var Configuracion $configuracion */
        $configuracion = $entityManager->getRepository(Configuracion::class)->findOneBy(array("usuario"=>$user));
        $form = $this->createForm(ConfiguracionType::class, $configuracion);
        $form->handleRequest($request);

        $errors = $form->getErrors(true);
        
        if ($form->isSubmitted() && $form->isValid()) {           
            if($form->get('password')->getData() != null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
            }            
            if($configuracion == null){
                $configuracion = new Configuracion();
                $configuracion->setUsuario($user);
            }
            if($form->get("avisar")->getData() != null){
                $configuracion->setAvisar(true);
            }else{
                $configuracion->setAvisar(false);
            }
            $entityManager->persist($configuracion);
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
