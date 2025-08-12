<?php
namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Etat;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Sortie;

final class SortieFiltreApi implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Sortie::class) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf('%s.etat NOT IN (:etatExclu)', $rootAlias))
            ->setParameter('etatExclu', [Etat::TERMINEE->value, Etat::HISTORISEE->value, Etat::EN_CREATION->value]);
    }
}
