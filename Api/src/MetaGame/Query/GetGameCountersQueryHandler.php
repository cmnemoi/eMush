<?php

declare(strict_types=1);

namespace Mush\MetaGame\Query;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\MetaGame\ViewModel\GameCountersViewModel;
use Mush\Player\Enum\EndCauseEnum;

final readonly class GetGameCountersQueryHandler
{
    private const array MUSH_ELIMINATION_CAUSES = [
        EndCauseEnum::ASSASSINATED,
        EndCauseEnum::BEHEADED,
        EndCauseEnum::BLED,
        EndCauseEnum::INJURY,
        EndCauseEnum::ROCKETED,
    ];

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function execute(GetGameCountersQuery $query): GameCountersViewModel
    {
        return new GameCountersViewModel(
            daedalusesInGame: $this->countDaedalusesInGame(),
            mushKilled: $this->countMushKilled(),
            messagesSent: $this->countMessagesSent(),
            expeditionsStarted: $this->countExpeditionsStarted(),
        );
    }

    private function countDaedalusesInGame(): int
    {
        return (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(daedalusInfo.id)
            FROM \Mush\Daedalus\Entity\DaedalusInfo daedalusInfo
            JOIN daedalusInfo.closedDaedalus closedDaedalus
            WHERE daedalusInfo.gameStatus = :gameStatus
            AND closedDaedalus.isCheater = false
            DQL)
            ->setParameter('gameStatus', GameStatusEnum::CURRENT)
            ->getSingleScalarResult();
    }

    private function countMushKilled(): int
    {
        return (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(closedPlayer.id)
            FROM \Mush\Player\Entity\ClosedPlayer closedPlayer
            LEFT JOIN closedPlayer.closedDaedalus closedDaedalus
            WHERE closedPlayer.isMush = true
            AND closedPlayer.finishedAt IS NOT NULL
            AND closedPlayer.endCause IN (:mushEliminationCauses)
            AND (closedDaedalus.id IS NULL OR closedDaedalus.isCheater = false)
            DQL)
            ->setParameter('mushEliminationCauses', self::MUSH_ELIMINATION_CAUSES)
            ->getSingleScalarResult();
    }

    private function countMessagesSent(): int
    {
        return (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(message.id)
            FROM \Mush\Chat\Entity\Message message
            JOIN message.channel channel
            JOIN channel.daedalusInfo daedalusInfo
            JOIN daedalusInfo.closedDaedalus closedDaedalus
            WHERE (message.author IS NOT NULL OR message.pirateAuthor IS NOT NULL)
            AND closedDaedalus.isCheater = false
            DQL)->getSingleScalarResult();
    }

    private function countExpeditionsStarted(): int
    {
        return (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(closedExploration.id)
            FROM \Mush\Exploration\Entity\ClosedExploration closedExploration
            JOIN closedExploration.daedalusInfo daedalusInfo
            JOIN daedalusInfo.closedDaedalus closedDaedalus
            WHERE closedDaedalus.isCheater = false
            DQL)->getSingleScalarResult();
    }
}
