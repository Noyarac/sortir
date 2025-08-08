<?php

namespace App\Command;

use App\Entity\Etat;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cloturer',
    description: 'Cloturer les sorties dont la date limite est atteinte.',
)]
class CloturerSortiesCommand extends Command
{
    public function __construct(private readonly SortieRepository $sortieRepository, private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln("Début de l'execution.");
        $sorties = $this->sortieRepository->findAllToCloture();
        $io->info(sizeof($sorties) . " sorties à cloturer ont été trouvées.");
        foreach ($sorties as $sortie) {
            $sortie->setEtat(Etat::CLOTUREE->value);
            $this->em->persist($sortie);
            $io->info($sortie->getNom() . " a été cloturée.");
        }
        $this->em->flush();
        $io->writeln("Fin de l'execution de la commande.");
        return Command::SUCCESS;
    }
}
