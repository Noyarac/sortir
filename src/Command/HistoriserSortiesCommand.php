<?php

namespace App\Command;

use App\Service\SortieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:historiser-sorties',
    description: "Permet de mettre l'état des sorties réalisées depuis plus d'un mois à 'historisée' " ,
)]
class HistoriserSortiesCommand extends Command
{
    public function __construct(private readonly SortieService $sortieService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription("Historise les sorties terminées depuis plus d'un mois")
            ->setHelp("Cette commande met à jour les sorties pour les passer en état 'historisée'")
            /*->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')*/
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbSortiesHistorisees = $this->sortieService->mettreAjourSortiesHistorisees();

        $io->success($nbSortiesHistorisees."sortie(s) ont été historisées");

        return Command::SUCCESS;
    }
}
