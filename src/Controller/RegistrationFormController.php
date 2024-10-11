<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationFormController extends AbstractController
{
    #[Route('/registration/form', name: 'app_registration_form')]
    public function index(): Response
    {
        return $this->render('registration_form/index.html.twig', [
            'controller_name' => 'RegistrationFormController',
        ]);
    }
}
