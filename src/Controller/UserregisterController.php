<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserregisterController extends AbstractController
{
    #[Route('/userregister', name: 'app_userregister')]
    public function index(): Response
    {
        return $this->render('userregister/index.html.twig');
    }
}
