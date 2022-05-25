<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BestSellerController extends AbstractController
{
    #[Route('/bestseller', name: 'app_best_seller')]
    public function index(): Response
    {
        return $this->render('best_seller/index.html.twig');
    }
}
