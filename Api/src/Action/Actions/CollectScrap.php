<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AvailableScrapToCollect;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CollectScrap extends AbstractAction
{
    protected string $name = ActionEnum::COLLECT_SCRAP;

    private ProbaCollection $numberOfScrapToCollect;

    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private PlaceServiceInterface $placeService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        PlaceServiceInterface $placeService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->placeService = $placeService;

        $this->numberOfScrapToCollect = new ProbaCollection([
            1 => 1,
            2 => 1,
            3 => 1,
        ]);
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new AvailableScrapToCollect(['groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['type' => PlaceTypeEnum::PATROL_SHIP, 'groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $spaceContent = $this->player->getDaedalus()->getSpace()->getEquipments();
        $numberOfScrapToCollect = (int) $this->randomService->getSingleRandomElementFromProbaCollection($this->numberOfScrapToCollect);
        if (!$numberOfScrapToCollect) {
            throw new \RuntimeException('There should be at least one scrap to collect if CollectScrap action is called');
        }
        $scrapToCollect = $this->randomService->getRandomElements($spaceContent->toArray(), $numberOfScrapToCollect);

        /** @var GameEquipment $scrap */
        foreach ($scrapToCollect as $scrap) {
            $this->moveScrapToPasiphae($scrap);
            $this->createCollectScrapLog($scrap);
        }
    }

    private function createCollectScrapLog(GameEquipment $scrap): void
    {
        $pilot = $this->player;
        $daedalus = $pilot->getDaedalus();
        $pasiphae = $this->parameter;
        if (!$pasiphae instanceof GameEquipment) {
            throw new \RuntimeException('Pasiphae should be an equipment');
        }
        $pasiphaePlace = $this->placeService->findByNameAndDaedalus($pasiphae->getName(), $daedalus);
        if (!$pasiphaePlace) {
            throw new \RuntimeException('Daedalus should have a Pasiphae place');
        }

        $logParameters = [
            $pilot->getLogKey() => $pilot->getLogName(),
            $pasiphae->getLogKey() => $pasiphae->getLogName(),
            'target_' . $scrap->getLogKey() => $scrap->getLogName(),
        ];

        $this->roomLogService->createLog(
            LogEnum::SCRAP_COLLECTED,
            $pasiphaePlace,
            VisibilityEnum::PUBLIC,
            'event_log',
            $pilot,
            $logParameters,
            new \DateTime()
        );
    }

    private function moveScrapToPasiphae(GameEquipment $scrap): void
    {
        $daedalus = $this->player->getDaedalus();
        $pasiphae = $this->parameter;
        if (!$pasiphae instanceof GameEquipment) {
            throw new \RuntimeException('Pasiphae should be an equipment');
        }
        $pasiphaePlace = $this->placeService->findByNameAndDaedalus($pasiphae->getName(), $daedalus);
        if (!$pasiphaePlace) {
            throw new \RuntimeException('Daedalus should have a Pasiphae place');
        }

        $moveEquipmentEvent = new MoveEquipmentEvent(
            equipment: $scrap,
            newHolder: $pasiphaePlace,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }
}
