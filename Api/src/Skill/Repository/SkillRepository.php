<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Skill\Entity\Skill;

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
}
