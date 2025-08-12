<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserImportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CampusRepository $campusRepository
    ){}

    public function importFromFile(string $filePath) : int
    {
        if(!file_exists($filePath)){
            throw new \RuntimeException("Fichier introuvable");
        }

        $handle=fopen($filePath, 'r');
        if($handle == false){
            throw new \RuntimeException("Impossible d'ouvrir le fichier.");
        }

        $this->entityManager->beginTransaction();

        $errorsList = [];
        $nbUtilisateursCrees = 0;
        $header = null;


        try {
            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                //On lit d'abord a 1ère ligne, si $header est null alors on stock la 1ère ligne dans $header
                if ($header === null) {
                    // On stocke cette première ligne comme tableau des noms de colonnes
                    $header = array_map('trim', $row);
                    continue; // On passe à la ligne suivante
                }

                // Les autres lignes sont associées aux noms de colonnes
                $data = array_combine($header, array_map('trim', $row));

                //On créé un utilisateur avec les données du fichier
                $user = new User();
                $campus = $this->campusRepository->findOneBy(['nom' => $data['campus']]);
                if (!$campus) {
                    $errorsList[] = "Campus '{$data['campus']}' introuvable.";
                    continue;
                }
                $user->setCampus($campus);
                $user->setPseudo($data['prenom'] . $data['nom']);
                $user->setPrenom($data['prenom']);
                $user->setNom($data['nom']);
                $user->setEmail($data['email']);
                $user->setPlainPassword('Mdp*123456');

                //validation des données avec Validator
                $errors = $this->validator->validate($user);
                if (count($errors) > 0) {
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage());
                    }
                    $errorsList[] = "Erreur de validation pour l'utilisateur {$user->getEmail()} : " . implode('; ', $errorMessages);
                    continue;
                }

                //Hashage du mot de passe et réinitialisation de PlainPassword pour ne pas garder en mémoire le mot de passe en clair
                $password = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $user->setPlainPassword(null);

                $this->entityManager->persist($user);
                $nbUtilisateursCrees++;
            }

            if (!empty($errorsList)) {
                $this->entityManager->rollback();
                $message = "Erreurs lors de l'import :\n" . implode("\n", $errorsList);
                throw new \RuntimeException($message);
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
            return $nbUtilisateursCrees;
        } catch (\Exception $e) {
            //En cas d'exception non prévue, roolback pour annuler la transaction ouverte
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $e;
        } finally {
            fclose($handle);
        }

    }
}
