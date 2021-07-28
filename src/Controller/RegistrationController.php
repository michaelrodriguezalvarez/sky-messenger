<?php

namespace App\Controller;

use App\Entity\Actividad;
use App\Entity\Configuracion;
use App\Entity\Perfil;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\CardScheme;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);        

        $errors = $form->getErrors(true);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $conn = $this->getDoctrine()->getConnection();
            $conn->beginTransaction();

            $user->setRoles(array('ROLE_USER'));

            $perfil = new Perfil();
            $perfil->setNick($request->request->get("registration_form")["nick"]);
            $perfil->setSexo($request->request->get("registration_form")["sexo"]);
            $perfil->setUsuario($user);

            $actividad = new Actividad();
            $actividad->setUsuario($user);
            $actividad->setEstado(true);

            $configuracion = new Configuracion();
            $configuracion->setUsuario($user);
            $configuracion->setAvisar(false);
            
            $entityManager->persist($perfil->getUsuario());
            $entityManager->persist($actividad);
            $entityManager->persist($configuracion);
            $entityManager->persist($perfil);

            $entityManager->flush();

            try 
            {             
                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('sky@localhost.com', 'Sky Messenger'))
                    ->to($user->getEmail())
                    ->subject('Por favor confirme su registro en Sky Messenger')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                
                // do anything else you need here, like send an email
                $conn->commit();
                $this->addFlash('success', 'Creación de cuenta completada. Por favor, revise su correo electrónico para activar su cuenta.');
                return $this->redirectToRoute('app_login');
            } catch (TransportException $th) {
                $conn->rollBack();
                if(str_contains($th->getMessage(), "with message \"550 Address") == true){
                    $this->addFlash('verify_email_error', 'La dirección de correo especificada no existe. Verifique la misma e intente nuevamente.');            
                }else{
                    $this->addFlash('verify_email_error', 'El servidor de correo nuestro no está disponible, intente más tarde. Perdone las molestias ocasionadas.');            
                }
            }
        }     

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Se ha verificado satisfactoriamente su correo.');

        return $this->redirectToRoute('app_login');
    }
}
