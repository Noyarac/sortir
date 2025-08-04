<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class UserController extends AbstractController
{
    #[Route('/user/modifier', name: 'user_gererSonProfil', methods: ['GET','POST'])]
    public function gererSonProfil(): Response
    {
        return $this->render('user/gererSonProfil.html.twig', [
        ]);
    }
}
