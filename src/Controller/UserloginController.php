<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserloginController extends AbstractController
{
    #[Route('/userlogin', name: 'app_userlogin')]
    public function index(): Response
    {
        return $this->render('userlogin/index.html.twig');
    }
}
