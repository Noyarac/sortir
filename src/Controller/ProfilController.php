<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/profil')]
final class ProfilController extends AbstractController
{
    #[Route('/{id}/modifier', name: 'profil_edit', methods: ['GET','POST'], requirements: ['id'=>'\d+'])]
    public function edit(): Response
    {
        return $this->render('profil/edit.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
