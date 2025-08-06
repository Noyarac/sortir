<?php

namespace App\Controller;

use App\Entity\Lieu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LieuController extends AbstractController
{
    #[Route('/lieu/{id}', name: 'lieu_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Lieu $lieu): JsonResponse
    {
        $ville = $lieu->getVille();
        $villeNom = $ville->getNom();
        $villeCP = $ville->getCodePostal();

        return new JsonResponse([
            'rue' => $lieu->getRue(),
            'ville' => $villeNom,
            'codePostal' => $villeCP,
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude()
        ]);
    }
}
