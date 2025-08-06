<?php

namespace App\Controller;

use App\Entity\Lieu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LieuController extends AbstractController
{
    #[Route('/lieu/{idLieu}', name: 'lieu_details', requirements: ['idLieu' => '\d+'], methods: ['GET'])]
    public function details(Lieu $lieu): JsonResponse
    {
        return new JsonResponse([
            'rue' => $lieu->getRue(),
            'ville' => $lieu->getVille()->getNom(),
            'codePostal' => $lieu->getVille()->getCodePostal(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude()
        ]);
    }
}
