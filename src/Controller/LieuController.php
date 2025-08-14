<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/creer', name:'lieu_creer', methods: ['POST'])]
    public function creerLieu(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        $lieuForm->handleRequest($request);
        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            // pour améliorer l'UX on retourne le lieu créé ainsi que la ville correspondante
            return $this->json([
                'success' => true,
                'lieu' => [
                    'id' => $lieu->getId(),
                    'nom' => $lieu->getNom(),
                    'idVille' => $lieu->getVille()->getId(),
                    ]
            ]);
        }

        //Formulaire invalide-> on envoi les erreurs
        $errors = [];
        foreach ($lieuForm->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field][] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'errors' => $errors
        ]);
    }
}
