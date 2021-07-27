<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\Configuracion;
use App\Entity\Perfil;
use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\ActividadRepository;
use App\Repository\PerfilRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    private $passwordEncoder;
    private $perfilRepository;

    public function __construct(PerfilRepository $perfilRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->perfilRepository = $perfilRepository;
    }
    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {        
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));

        return $this->render('admin/index.html.twig', [
            'perfil' => $perfil,
        ]);
    }

    /**
     * @Route("/admin/user/index", name="app_admin_user_index")
     */
    public function user_index(UserRepository $userRepository): Response
    {        
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));

        return $this->render('admin/user_index.html.twig', [
            'perfil' => $perfil,
            'perfiles' => $this->perfilRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/user/lock", name="app_admin_user_lock")
     */
    public function user_lock(UserRepository $userRepository): Response
    {        
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));

        $actividades = $this->getDoctrine()->getRepository(Actividad::class)->findAll();

        return $this->render('admin/user_lock.html.twig', [
            'perfil' => $perfil,
            'actividades' => $actividades
        ]);
    }

    /**
     * @Route("/admin/user/lock/execute/{id}/{estado}", name="app_admin_user_lock_execute")
     */
    public function user_lock_execute(User $user, bool $estado): Response
    {      
        if($user == $this->getUser()){
            $this->addFlash('errors', 'No se puede cambiar el estado del usuario autenticado.');
        }else{
            $entityManager = $this->getDoctrine()->getManager();
            /**@var Actividad $actividad */
            $actividad = $this->getDoctrine()->getRepository(Actividad::class)->findOneBy(array('usuario'=>$user->getId()));
            $actividad->setEstado($estado);
            $entityManager->flush();
        }
            
        return $this->redirectToRoute('app_admin_user_lock');
    }

    /**
     * @Route("/admin/user/show/{id}", name="app_admin_user_show")
     */
    public function user_show(User $user, UserRepository $userRepository, ActividadRepository $actividadRepository): Response
    {        
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));

        $perfil_mostrar = $this->perfilRepository->findOneBy(array('usuario'=>$user->getId()));
        $actividad_mostrar = $actividadRepository->findOneBy(array('usuario'=>$user->getId()));
        
        return $this->render('admin/user_show.html.twig', [
            'perfil' => $perfil,
            'user_mostrar' => $user,
            'perfil_mostrar' => $perfil_mostrar,
            'actividad_mostrar' => $actividad_mostrar
        ]);
    }

    /**
     * @Route("/admin/user/new", name="app_admin_user_new", methods={"GET","POST"})
     */
    public function user_new(Request $request): Response
    {
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));

        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user->setRoles(array($form->get('roles')->getData()));            
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $user->setIsVerified(true);

            /**@var Perfil $perfil_nuevo */
            $perfil_nuevo = new Perfil();
            $perfil_nuevo->setUsuario($user);
            $perfil_nuevo->setNick($form->get('nick')->getData());
            $perfil_nuevo->setNombre($form->get('nombre')->getData());
            $perfil_nuevo->setApellidos($form->get('apellidos')->getData());
            $perfil_nuevo->setDireccion($form->get('direccion')->getData());
            $perfil_nuevo->setTelefono($form->get('telefono')->getData());
            $perfil_nuevo->setSexo($form->get('sexo')->getData());
            $perfil_nuevo->setDescripcion($form->get('descripcion')->getData());

            /**@var Configuracion $configuracion */
            $configuracion = new Configuracion();
            $configuracion->setUsuario($user);
            $configuracion->setAvisar(false);

            /**@var Actividad $actividad */
            $actividad = new Actividad();
            $actividad->setUsuario($user);
            $actividad->setEstado($form->get('estado')->getData());

            try {
                $entityManager->persist($user);
                $entityManager->persist($perfil_nuevo);
                $entityManager->persist($configuracion);
                $entityManager->persist($actividad);
                $entityManager->flush();

                $this->addFlash('success', 'Usuario adicionado satisfactoriamente.');
                return $this->redirectToRoute('app_admin_user_new');
            } catch (UniqueConstraintViolationException $pdoe) {
                if(str_contains($pdoe->getMessage(), "unique_nick") == true){
                    $form->get('nick')->addError(new FormError('Debe especificar un nick diferente para el usuario.'));
                    $errors = $form->getErrors(true);                                  
                }
            }
        }

        return $this->render('admin/user_new.html.twig', [
            'perfil' => $perfil,
            'user' => $user,
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="app_admin_user_edit", methods={"GET","POST"})
     */
    public function user_edit(Request $request, User $user): Response
    {
        $current_user = $this->getUser();
        $perfil = $this->perfilRepository->findOneBy(array('usuario'=>$current_user->getId()));
       
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user->setRoles(array($form->get('roles')->getData()));            
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            $user->setIsVerified(true);

            /**@var Perfil $perfil_nuevo */
            $perfil_nuevo = new Perfil();
            $perfil_nuevo->setUsuario($user);
            $perfil_nuevo->setNick($form->get('nick')->getData());
            $perfil_nuevo->setNombre($form->get('nombre')->getData());
            $perfil_nuevo->setApellidos($form->get('apellidos')->getData());
            $perfil_nuevo->setDireccion($form->get('direccion')->getData());
            $perfil_nuevo->setTelefono($form->get('telefono')->getData());
            $perfil_nuevo->setSexo($form->get('sexo')->getData());
            $perfil_nuevo->setDescripcion($form->get('descripcion')->getData());

            /**@var Configuracion $configuracion */
            $configuracion = new Configuracion();
            $configuracion->setUsuario($user);
            $configuracion->setAvisar(false);

            /**@var Actividad $actividad */
            $actividad = new Actividad();
            $actividad->setUsuario($user);
            $actividad->setEstado($form->get('estado')->getData());

            try {
                $entityManager->persist($user);
                $entityManager->persist($perfil_nuevo);
                $entityManager->persist($configuracion);
                $entityManager->persist($actividad);
                $entityManager->flush();

                $this->addFlash('success', 'Usuario editado satisfactoriamente.');                
            } catch (UniqueConstraintViolationException $pdoe) {
                if(str_contains($pdoe->getMessage(), "unique_nick") == true){
                    $form->get('nick')->addError(new FormError('Debe especificar un nick diferente para el usuario.'));
                    $errors = $form->getErrors(true);                                  
                }
            }
        }

        return $this->render('admin/user_edit.html.twig', [
            'perfil' => $perfil,
            'user' => $user,
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }
}
