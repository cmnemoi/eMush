<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractUpgradeDrone extends AbstractAction
{
    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        protected RoomLogServiceInterface $roomLogService,
        protected StatusServiceInterface $statusService,
        protected TranslationServiceInterface $translationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Drone;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasSkill([
                'skill' => SkillEnum::ROBOTICS_EXPERT,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasEquipment([
                'reach' => ReachEnum::ROOM,
                'equipments' => [ItemEnum::METAL_SCRAPS],
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DRONE_UPGRADE_LACK_RESSOURCES,
                'number' => 2,
            ]),
        ]);
    }

    abstract public function upgradeStatus(): string;

    abstract public function upgradeLog(): string;

    abstract public function upgradeName(): string;

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createUpgradeStatus();
        $this->createUpgradeLog();
        $this->destroyScrapMetal();
    }

    private function createUpgradeStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: $this->upgradeStatus(),
            holder: $this->drone(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createUpgradeLog(): void
    {
        $this->roomLogService->createLog(
            logKey: $this->upgradeLog(),
            place: $this->drone()->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'actions_log',
            player: $this->player,
            parameters: [
                'target_drone' => $this->translatedDroneName(),
                $this->player->getLogKey() => $this->player->getAnonymousKeyOrLogName(),
            ],
            dateTime: new \DateTime(),
        );
    }

    private function translatedDroneName(): string
    {
        return $this->translationService->translate(
            key: 'drone',
            parameters: [
                'drone_nickname' => $this->drone()->getNickname(),
                'drone_serial_number' => $this->drone()->getSerialNumber(),
            ],
            domain: 'event_log',
            language: $this->drone()->getDaedalus()->getLanguage()
        );
    }

    private function destroyScrapMetal(): void
    {
        $isScrapMetal = static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === ItemEnum::METAL_SCRAPS;

        $playerScrapMetal = $this->player->getEquipments()->filter($isScrapMetal);
        $roomScrapMetal = $this->player->getPlace()->getEquipments()->filter($isScrapMetal);

        $totalScrapDestroyed = $this->destroyFromCollection($playerScrapMetal, quantity: $this->getOutputQuantity());

        if ($totalScrapDestroyed < $this->getOutputQuantity()) {
            $this->destroyFromCollection($roomScrapMetal, quantity: $this->getOutputQuantity() - $totalScrapDestroyed);
        }
    }

    /** @param Collection<array-key, GameEquipment> $scrapCollection */
    private function destroyFromCollection(Collection $scrapCollection, int $quantity): int
    {
        $scrapDestroyed = 0;
        $scrap = $scrapCollection->slice(0, $quantity);

        foreach ($scrap as $scrapMetal) {
            $this->triggerDestroyEvent($scrapMetal);
            ++$scrapDestroyed;
        }

        return $scrapDestroyed;
    }

    private function triggerDestroyEvent(GameEquipment $scrapMetal): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $scrapMetal,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function drone(): Drone
    {
        $drone = $this->gameEquipmentTarget();
        if ($drone instanceof Drone) {
            return $drone;
        }

        throw new \RuntimeException('Target is not a drone');
    }
}
