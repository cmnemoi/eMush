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
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CollectScrap extends AbstractAction
{
    protected string $name = ActionEnum::COLLECT_SCRAP;

    // TODO: put those collections in the future PatrolShip Mechanic
    private ProbaCollection $numberOfScrapToCollect;
    private ProbaCollection $pilotDamage;

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
        $this->pilotDamage = new ProbaCollection([
            2 => 1,
            3 => 1,
            4 => 1,
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
        $daedalus = $this->player->getDaedalus();
        $spaceContent = $daedalus->getSpace()->getEquipments();
        $numberOfScrapToCollect = (int) $this->randomService->getSingleRandomElementFromProbaCollection($this->numberOfScrapToCollect);
        if (!$numberOfScrapToCollect) {
            throw new \RuntimeException('There should be at least one scrap to collect if CollectScrap action is called');
        }
        $scrapToCollect = $this->randomService->getRandomElements($spaceContent->toArray(), $numberOfScrapToCollect);

        /** @var GameEquipment $scrap */
        foreach ($scrapToCollect as $scrap) {
            $this->moveScrapToPasiphae($scrap);
            $this->createCollectScrapLog($scrap);
            if ($daedalus->getAttackingHunters()->count() > 0) {
                $this->damagePilot();
            }
        }
    }

    private function createCollectScrapLog(GameEquipment $scrap): void
    {
        /** @var GameEquipment $pasiphae */
        $pasiphae = $this->parameter;
        $pasiphaePlace = $this->getPasiphaePlace();
        $pilot = $this->player;

        $logParameters = [
            $pilot->getLogKey() => $pilot->getLogName(),
            $pasiphae->getLogKey() => $pasiphae->getLogName(),
            'target_' . $scrap->getLogKey() => $scrap->getLogName(),
        ];

        $this->roomLogService->createLog(
            logKey: LogEnum::SCRAP_COLLECTED,
            place: $pasiphaePlace,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $pilot,
            parameters: $logParameters,
            dateTime: new \DateTime()
        );
    }

    private function damagePilot(): void
    {
        $pasiphaePlace = $this->getPasiphaePlace();
        $pilot = $this->player;

        if ($this->randomService->randomPercent() >= $this->action->getCriticalRate()) {
            return;
        }
        $damage = intval($this->randomService->getSingleRandomElementFromProbaCollection($this->pilotDamage));

        $this->roomLogService->createLog(
            logKey: LogEnum::ATTACKED_BY_HUNTER,
            place: $pasiphaePlace,
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $pilot,
            dateTime: new \DateTime()
        );

        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$damage,
            tags: $this->getAction()->getActionTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerVariableEvent, PlayerVariableEvent::CHANGE_VARIABLE);
    }

    private function moveScrapToPasiphae(GameEquipment $scrap): void
    {
        $pasiphaePlace = $this->getPasiphaePlace();
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

    private function getPasiphaePlace(): Place
    {
        /** @var GameEquipment $pasiphae */
        $pasiphae = $this->parameter;
        $pasiphaePlace = $this->placeService->findByNameAndDaedalus($pasiphae->getName(), $pasiphae->getDaedalus());
        if (!$pasiphaePlace) {
            throw new \RuntimeException('Daedalus should have a Pasiphae place');
        }

        return $pasiphaePlace;
    }
}
