<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfiguracionController extends AbstractController
{
    /**
     * @Route("/configuracion", name="configuracion")
     */
    public function index(): Response
    {
        return $this->render('configuracion/index.html.twig', [
            'error' => null,
        ]);
    }
}
