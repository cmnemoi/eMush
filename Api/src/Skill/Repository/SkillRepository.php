<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

/**
 * @template-extends ServiceEntityRepository<Skill>
 */
final class SkillRepository extends ServiceEntityRepository implements SkillRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    public function delete(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $player->removeSkill($skill);

        $this->_em->remove($skill);
        $this->_em->flush();
    }

    public function countSkill(SkillEnum $skillName): int
    {
        $queryBuilder = $this->createQueryBuilder('skill');

        $queryBuilder->select('skill')
            ->innerJoin('skill.skillConfig', 'skillConfig')
            ->where('skillConfig.name = :skillName')
            ->setParameter('skillName', $skillName);

        return \count($queryBuilder->getQuery()->getArrayResult());
    }

    public function countAllSkill(): int
    {
        $queryBuilder = $this->createQueryBuilder('skill');

        $queryBuilder->select('skill');

        return \count($queryBuilder->getQuery()->getArrayResult());
    }

    public function countSkillByCharacter(string $characterName): array
    {
        $queryBuilder = $this->createQueryBuilder('skill');

        $queryBuilder->select('skill')
            ->innerJoin('skill.player', 'player')
            ->innerJoin('player.playerInfo', 'playerInfo')
            ->innerJoin('playerInfo.characterConfig', 'characterConfig')
            ->where('characterConfig.name = :characterName')
            ->setParameter('characterName', $characterName);

        return $queryBuilder->getQuery()->getResult();
    }
}
