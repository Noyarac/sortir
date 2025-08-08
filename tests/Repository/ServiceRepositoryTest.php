<?php

namespace App\Tests\Repository;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\DTO\FiltreSortie;
use App\Repository\CampusRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceRepositoryTest extends KernelTestCase
{
    protected static ?EntityManagerInterface $entityManager = null;
    protected static ?SortieRepository $sortieRepository = null;
    protected static ?CampusRepository $campusRepository = null;
    protected static ?LieuRepository $lieuRepository = null;
    protected static ?UserRepository $userRepository = null;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        self::$sortieRepository = self::$entityManager->getRepository(Sortie::class);
        self::$campusRepository = self::$entityManager->getRepository(Campus::class);
        self::$lieuRepository = self::$entityManager->getRepository(Lieu::class);
        self::$userRepository = self::$entityManager->getRepository(User::class);
    }

    public function test_findSortiesTermineesDepuisPlusDeNbMois(): void
    {
        $entityManager = self::$entityManager;
        $sortieRepository = self::$sortieRepository;
        $campus = self::$campusRepository->find(1);
        $lieu = self::$lieuRepository->find(1);
        $user = self::$userRepository->find(1);

        $sortieHistorisable = new Sortie();
        $sortieHistorisable->setNom('Sortie Historisable');
        $sortieHistorisable->setDateHeureDebut((new \DateTimeImmutable())->modify('-2 days')->modify('-1 month'));
        $sortieHistorisable->setDateLimiteInscription((new \DateTimeImmutable())->modify('-4 days')->modify('-1 month'));
        $sortieHistorisable->setNbInscriptionMax(15);
        $sortieHistorisable->setDuree(60);
        $sortieHistorisable->setInfosSortie('Cette sortie est historisable');
        $sortieHistorisable->setCampus($campus);
        $sortieHistorisable->setLieu($lieu);
        $sortieHistorisable->setOrganisateur($user);
        $sortieHistorisable->setEtat(Etat::TERMINEE->value);
        $entityManager->persist($sortieHistorisable);

        $sortieNonHistorisable = new Sortie();
        $sortieNonHistorisable->setNom('Sortie Non Historisable');
        $sortieNonHistorisable->setDateHeureDebut((new \DateTimeImmutable())->modify('-2 days'));
        $sortieNonHistorisable->setDateLimiteInscription((new \DateTimeImmutable())->modify('-4 days'));
        $sortieNonHistorisable->setNbInscriptionMax(15);
        $sortieNonHistorisable->setDuree(60);
        $sortieNonHistorisable->setInfosSortie('Cette sortie est non historisable');
        $sortieNonHistorisable->setCampus($campus);
        $sortieNonHistorisable->setLieu($lieu);
        $sortieNonHistorisable->setOrganisateur($user);
        $sortieNonHistorisable->setEtat(Etat::TERMINEE->value);
        $entityManager->persist($sortieNonHistorisable);

        $entityManager->flush();

        $results = $sortieRepository->findSortiesTermineesDepuisPlusDeNbMois(1);

        $this->assertCount(1, $results);
        $this->assertSame($sortieHistorisable->getId(), $results[0]->getId());

        $entityManager->remove($sortieNonHistorisable);
        $entityManager->remove($sortieHistorisable);
        $entityManager->flush();
    }

    public function test_Cherche_sortie_a_cloturer(): void
    {
        $entityManager = self::$entityManager;
        $sortieRepository = self::$sortieRepository;
        $sortie = new Sortie();
        $sortie->setDateLimiteInscription(new DateTimeImmutable("-1 week"));
        $sortie->setNom("Sortie à tester");
        $sortie->setDateHeureDebut(new DateTimeImmutable("3 weeks"));
        $sortie->setNbInscriptionMax(15);
        $sortie->setDuree(60);
        $sortie->setInfosSortie('Cette sortie est à cloturer');
        $sortie->setCampus(self::$campusRepository->findOneBy([]));
        $sortie->setLieu(self::$lieuRepository->findOneBy([]));
        $sortie->setOrganisateur(self::$userRepository->findOneBy([]));
        $sortie->setEtat(Etat::OUVERTE->value);
        $entityManager->persist($sortie);

        $sortie2 = new Sortie();
        $sortie2->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie2->setNom("Sortie groupe temoin");
        $sortie2->setDateHeureDebut(new DateTimeImmutable("3 weeks"));
        $sortie2->setNbInscriptionMax(15);
        $sortie2->setDuree(60);
        $sortie2->setInfosSortie("Cette sortie n'est pas à cloturer");
        $sortie2->setCampus(self::$campusRepository->findOneBy([]));
        $sortie2->setLieu(self::$lieuRepository->findOneBy([]));
        $sortie2->setOrganisateur(self::$userRepository->findOneBy([]));
        $sortie2->setEtat(Etat::OUVERTE->value);
        $entityManager->persist($sortie2);

        $entityManager->flush();
        $results = $sortieRepository->findAllToCloture();

        $this->assertContains($sortie, $results);
        $this->assertNotContains($sortie2, $results);
    }

    public function test_Cherche_sorties_filtrees(): void
    {
        $entityManager = self::$entityManager;
        $sortieRepository = self::$sortieRepository;

        $campus = self::$campusRepository->findOneBy([]);
        $lieu = self::$lieuRepository->findOneBy([]);
        $organisateur = self::$userRepository->findOneBy([]);

        $sortie = new Sortie();
        $sortie->setDateLimiteInscription(new DateTimeImmutable("1 week"));
        $sortie->setNom("Sortie à tester");
        $sortie->setDateHeureDebut(new DateTimeImmutable("3 weeks"));
        $sortie->setNbInscriptionMax(15);
        $sortie->setDuree(60);
        $sortie->setInfosSortie('Cette sortie spéciale doit être retournée');
        $sortie->setCampus($campus);
        $sortie->setLieu($lieu);
        $sortie->setOrganisateur($organisateur);
        $sortie->setEtat(Etat::OUVERTE->value);
        $entityManager->persist($sortie);

        $sortie2 = new Sortie();
        $sortie2->setDateLimiteInscription(new DateTimeImmutable("2 weeks"));
        $sortie2->setNom("Sortie groupe temoin");
        $sortie2->setDateHeureDebut(new DateTimeImmutable("3 weeks"));
        $sortie2->setNbInscriptionMax(15);
        $sortie2->setDuree(60);
        $sortie2->setInfosSortie("Cette sortie ne doit pas être retournée");
        $sortie2->setCampus($campus);
        $sortie2->setLieu($lieu);
        $sortie2->setOrganisateur($organisateur);
        $sortie2->setEtat(Etat::OUVERTE->value);
        $entityManager->persist($sortie2);

        $entityManager->flush();

        $filtre = new FiltreSortie($campus, "tester");
        $results = $sortieRepository->findByFilter($filtre);
        $this->assertContains($sortie, $results);
        $this->assertNotContains($sortie2, $results);
    }
}
