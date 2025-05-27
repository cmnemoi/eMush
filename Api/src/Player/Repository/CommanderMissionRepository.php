<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\CommanderMission;

/**
 * @template-extends ServiceEntityRepository<CommanderMission>
 */
final class CommanderMissionRepository extends ServiceEntityRepository implements CommanderMissionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommanderMission::class);
    }

    public function findByIdOrThrow(int $id): CommanderMission
    {
        return $this->find($id) ?? throw new \RuntimeException("CommanderMission {$id} not found");
    }

    public function save(CommanderMission $commanderMission): void
    {
        $this->_em->persist($commanderMission);
        $this->_em->flush();
    }
}
