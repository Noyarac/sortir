<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu')]
final class LieuController extends AbstractController
{
    #[Route('/{id}', name: 'lieu_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Lieu $lieu): JsonResponse
    {
        $codePostal = $lieu->getVille()->getCodePostal();

        return new JsonResponse([
            'rue' => $lieu->getRue(),
            'codePostal' =>$codePostal,
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude()
        ]);
    }

    #[Route('/ville/{id}', name:'lieu_getLieuxSelonVille', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getLieuxSelonVille(Ville $ville): JsonResponse
    {
        $lieux = $ville->getLieux();
        $data = [];
        foreach ($lieux as $lieu) {
            $data[] = [
                'id'=>$lieu->getId(),
                'nom'=>$lieu->getNom(),
                ];
        }
        return $this->json($data);
    }

    #[Route('/', name:'lieu_getAllLieux', methods: ['GET'])]
    public function getAllLieux(EntityManagerInterface $entityManager): JsonResponse
    {
        $lieuRepository = $entityManager->getRepository(Lieu::class);
        $lieux = $lieuRepository->findBy([], ['nom' => 'ASC']);
        $data = [];
        foreach ($lieux as $lieu) {
            $data[] = [
                'id'=>$lieu->getId(),
                'nom'=>$lieu->getNom(),
            ];
        }
        return $this->json($data);
    }
}
