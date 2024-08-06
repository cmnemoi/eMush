<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;

/**
 * @template-extends ServiceEntityRepository<SkillConfig>
 */
final class SkillConfigRepository extends ServiceEntityRepository implements SkillConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillConfig::class);
    }

    public function findOneByNameAndDaedalusOrThrow(SkillEnum $skill, Daedalus $daedalus): SkillConfig
    {
        $query = <<<'EOD'
        SELECT skill_config.*
        FROM daedalus
        INNER JOIN daedalus_info ON daedalus.id = daedalus_info.daedalus_id
        INNER JOIN game_config_character_config ON daedalus_info.game_config_id = game_config_character_config.game_config_id
        INNER JOIN character_config ON game_config_character_config.character_config_id = character_config.id
        INNER JOIN character_config_skill_config ON character_config.id = character_config_skill_config.character_config_id
        INNER JOIN skill_config ON character_config_skill_config.skill_config_id = skill_config.id
        WHERE daedalus_id = :daedalusId
        AND skill_config.name = :skillName
        EOD;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(SkillConfig::class, 'skill_config');

        $query = $this->_em->createNativeQuery($query, $rsm);
        $query
            ->setParameter('daedalusId', $daedalus->getId())
            ->setParameter('skillName', $skill->toString());

        return $query->getOneOrNullResult() ?? throw new \RuntimeException("Skill {$skill->toString()} not found for daedalus {$daedalus->getId()}");
    }
}
