<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontHomeController extends AbstractController
{
    #[Route('/front/home', name: 'app_front_home')]
    public function index(): Response
    {
        return $this->render('front_home/index.html.twig', [
            'controller_name' => 'FrontHomeController',
        ]);
    }
}
