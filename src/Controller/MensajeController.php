<?php

namespace App\Controller;

use App\Entity\Configuracion;
use App\Entity\Mensaje;
use App\Entity\Perfil;
use App\Form\MensajeType;
use App\Repository\UserRepository;
use App\Repository\MensajeRepository;
use App\Repository\PerfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mensaje")
 */
class MensajeController extends AbstractController
{
    /**
     * @Route("/", name="mensaje_index", methods={"GET"})
     */
    public function index(): Response
    {
        $mensajes = $this->getDoctrine()
            ->getRepository(Mensaje::class)
            ->findAll();

        return $this->render('mensaje/index.html.twig', [
            'mensajes' => $mensajes,
        ]);
    }

    /**
     * @Route("/new", name="mensaje_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $mensaje = new Mensaje();
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('mensaje_index');
        }

        return $this->render('mensaje/new.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mensaje_show", methods={"GET"})
     */
    public function show(Mensaje $mensaje): Response
    {
        return $this->render('mensaje/show.html.twig', [
            'mensaje' => $mensaje,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="mensaje_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Mensaje $mensaje): Response
    {
        $form = $this->createForm(MensajeType::class, $mensaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mensaje_index');
        }

        return $this->render('mensaje/edit.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mensaje_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Mensaje $mensaje): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mensaje->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mensaje);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mensaje_index');
    }

    /**
     * @Route("/api/send", name="mensaje_enviar", methods={"POST"})
     */
    public function send(Request $request, UserRepository $userRepository, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        try {
            if($_POST){                
                $current_user = $this->getUser();
                if ($this->isCsrfTokenValid('enviar_mensaje'.$current_user->getId(), $request->request->get('_token'))) {
                    $mensaje = new Mensaje();
                    $mensaje->setTexto($request->request->get('texto'));
                    $mensaje->setFecha(new \DateTime());
                    $mensaje->setDestinatario($userRepository->findOneBy(array('id' => $request->request->get('destinatario'))));
                    $mensaje->setRemitente($this->getUser());
                    
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($mensaje);
                    $entityManager->flush();

                    $this->AvisarCorreoMensajesNuevos($mensaje,(new TemplatedEmail()),$mailer, $entityManager);
        
                    $response = new JsonResponse();
                    $response->setData([
                        'success' => true,
                        'data' => 'Message sent'
                    ]);
                    $response->setStatusCode(Response::HTTP_OK);
        
                    return $response;
                }else{
                    throw new BadRequestException(Response::HTTP_BAD_REQUEST);
                }
            }else{
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

    /**
     * @Route("/api/getPublicMessages", name="mensaje_index_public", methods={"GET"})
     */
    public function getPublicMessages(Request $request, MensajeRepository $mensajeRepository, PerfilRepository $perfilRepository): Response
    {
        try {
            $mensajesPublicos = $mensajeRepository->getPublicMessages();       
            $mensajes = [];

            /** @var Mensaje $mensaje */
            foreach ($mensajesPublicos as $mensaje) {
                /** @var Perfil $remitente */
                $remitente = $perfilRepository->findOneBy(array('usuario'=>$mensaje->getRemitente())); 
                array_push($mensajes, [
                    'id'=>$mensaje->getId(), 
                    'destinatario'=>null, 
                    'remitente'=>[
                        'id_usuario'=>$remitente->getUsuario()->getId(),
                        'nick'=>$remitente->getNick()
                    ],
                    'texto' => $mensaje->getTexto(),
                    'fecha' => $mensaje->getFecha()
                ]);
            }
            
            $response = new JsonResponse();
            $response->setData([
                'success' => true,
                'data' => $mensajes
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

    /**
     * @Route("/api/getPrivateMessages/{id_usuario_seleccionado}", name="mensaje_index_private", methods={"GET"})
     */
    public function getPrivateMessages(Request $request, int $id_usuario_seleccionado, MensajeRepository $mensajeRepository, PerfilRepository $perfilRepository): Response
    {
        try {
            /** @var User $current_user */
            $current_user = $this->getUser();
            $mensajesPrivados = $mensajeRepository->getPrivateMessages($id_usuario_seleccionado,$current_user->getId());
            $mensajes = [];

            /** @var Mensaje $mensaje */
            foreach ($mensajesPrivados as $mensaje) {
                /** @var Perfil $remitente */
                $remitente = $perfilRepository->findOneBy(array('usuario'=>$mensaje->getRemitente())); 
                $destinatario = $perfilRepository->findOneBy(array('usuario'=>$mensaje->getDestinatario())); 
                array_push($mensajes, [
                    'id'=>$mensaje->getId(), 
                    'destinatario'=>[
                        'id_usuario'=>$destinatario->getUsuario()->getId(),
                        'nick'=>$destinatario->getNick()
                    ], 
                    'remitente'=>[
                        'id_usuario'=>$remitente->getUsuario()->getId(),
                        'nick'=>$remitente->getNick()
                    ],
                    'texto' => $mensaje->getTexto(),
                    'fecha' => $mensaje->getFecha()
                ]);
            }
            
            $response = new JsonResponse();
            $response->setData([
                'success' => true,
                'data' => $mensajes
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

    /**
     * @Route("/api/mensaje/no_leidos/{id_remitente}", name="mensaje_no_leido_cantidad", methods={"GET"})
     */
    public function getCountUnreadMessages(Request $request, int $id_remitente, MensajeRepository $mensajeRepository): Response
    {       
        try {           
                /**@var User $current_user */
                $current_user = $this->getUser();                   
                $cantidad = $mensajeRepository->getUnreadMessages($current_user, $id_remitente);
                $response = new JsonResponse();
                $response->setData([
                    'success' => true,
                    'data' => $cantidad
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

    private function AvisarCorreoMensajesNuevos(Mensaje $mensaje, TemplatedEmail $email, MailerInterface $mailer, EntityManagerInterface $entityManager): void
    {
        if($mensaje->getDestinatario() != null){
            if($mensaje->getDestinatario()->isActiveNow() == false){
                $configuracion = $entityManager->getRepository(Configuracion::class)
                                    ->findOneBy(array("usuario"=>$mensaje->getDestinatario())); 
                if($configuracion->getAvisar() == true){
                    /**@var Perfil $perfil_remitente */
                    $perfil_remitente = $entityManager->getRepository(Perfil::class)
                                    ->findOneBy(array("usuario"=>$mensaje->getRemitente()));
                    /**@var Perfil $perfil_destinatario */
                    $perfil_destinatario = $entityManager->getRepository(Perfil::class)
                                    ->findOneBy(array("usuario"=>$mensaje->getDestinatario()));
                    $context = $email->getContext();
                    $context['nick'] = $perfil_destinatario->getNick();
                    $context['remitente'] = $perfil_remitente->getNick();
                    $context['contenido'] = $mensaje->getTexto();        
                    $email->context($context);        
                    $mailer
                        ->send(
                        $email
                        ->from(new Address('sky@localhost.com', 'Sky Messenger'))
                        ->to($mensaje->getDestinatario()->getEmail())
                        ->subject('Aviso de llegada de mensaje nuevo')
                        ->htmlTemplate('mensaje/aviso_mensajes.html.twig')
                    );
                }  
            }
        }      
    }
}
