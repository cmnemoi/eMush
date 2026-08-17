<?php

declare(strict_types=1);

namespace Mush\Action\Actions\SummerEventActions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceName;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SearchTreasure extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SEARCH_FOR_THE_TREASURE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new PlaceName([
                'places' => [RoomEnum::PLANET],
                'groups' => ['visibility'],
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => DaedalusStatusEnum::TREASURE_SECTOR,
                'target' => HasStatus::DAEDALUS,
                'contain' => true,
                'groups' => ['visibility'],
            ])
        );
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        $exploration = $this->getPlayer()->getDaedalus()->getExplorationOrThrow();
        $lastLog = $exploration->getClosedExploration()->getLogs()->getLastLog();

        $status = $this->getPlayer()->getDaedalus()->getChargeStatusByNameOrThrow(DaedalusStatusEnum::TREASURE_SECTOR);

        if (PlanetSectorEnum::getTreasureSectorFromCharge($status->getCharge()) === $lastLog->getPlanetSectorName()) {
            return new Success();
        }

        return new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if (!$result instanceof Success) {
            return;
        }

        // we create the chest
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::TREASURE_HUNT_CHEST_CLOSED,
            $this->getPlayer()->getPlace(),
            $this->getTags(),
            new \DateTime(),
            VisibilityEnum::PUBLIC,
        );

        // we remove the status from the ship so that we can't find the chest again
        $this->statusService->removeStatus(
            DaedalusStatusEnum::TREASURE_SECTOR,
            $this->getPlayer()->getDaedalus(),
            $this->getTags(),
            new \DateTime()
        );
    }
}
