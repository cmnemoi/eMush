<?php

namespace Mush\Action\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @template-extends ServiceEntityRepository<ActionConfig>
 */
class ActionConfigRepository extends ServiceEntityRepository implements ActionConfigRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActionConfig::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function findActionSuccessRateByDaedalusAndMechanicOrThrow(ActionEnum $action, Daedalus $daedalus, string $mechanic): int
    {
        $rawQuery = <<<'EOD'
            SELECT DISTINCT game_variable.value
            FROM action AS action_config
            INNER JOIN equipment_mechanic_action_config ON equipment_mechanic_action_config.action_config_id = action_config.id
            INNER JOIN equipment_mechanic ON equipment_mechanic.id = equipment_mechanic_action_config.equipment_mechanic_id
            INNER JOIN equipment_config_equipment_mechanic ON equipment_config_equipment_mechanic.equipment_mechanic_id = equipment_mechanic.id
            INNER JOIN equipment_config ON equipment_config.id = equipment_config_equipment_mechanic.equipment_config_id
            INNER JOIN game_config_equipment_config ON game_config_equipment_config.equipment_config_id = equipment_config.id
            INNER JOIN game_variable_collection ON game_variable_collection.id = action_config.id
            INNER JOIN game_variable ON game_variable.game_variable_collection_id = game_variable_collection.id
            WHERE game_config_equipment_config.game_config_id = :gameConfigId
            AND action_config.action_name = :actionName
            AND equipment_mechanic.type = :mechanic
            AND game_variable.name = 'percentageSuccess'
        EOD;

        $statement = $this->entityManager->getConnection()->prepare($rawQuery);

        $statement->bindValue('gameConfigId', $daedalus->getGameConfig()->getId());
        $statement->bindValue('actionName', $action->value);
        $statement->bindValue('mechanic', $mechanic);

        $result = $statement->executeQuery()->fetchOne();
        if (!$result) {
            throw new \RuntimeException("Action {$action->value} not found for Daedalus {$daedalus->getId()} and mechanic {$mechanic}");
        }

        return (int) $result;
    }

    public function save(ActionConfig $actionConfig): void
    {
        $this->entityManager->persist($actionConfig);
        $this->entityManager->flush();
    }
}
