<?php

namespace Mush\Player\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\ClosedPlayer;

final class ClosedPlayerExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (ClosedPlayer::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->leftJoin($rootAlias . '.closedDaedalus', 'closed_daedalus');
        $queryBuilder->leftJoin('closed_daedalus.daedalusInfo', 'daedalus_info');
        $queryBuilder->andWhere($queryBuilder->expr()->in('daedalus_info.gameStatus', ':gameStatus'));
        $queryBuilder->setParameter('gameStatus', [GameStatusEnum::FINISHED, GameStatusEnum::CLOSED]);
    }
}
