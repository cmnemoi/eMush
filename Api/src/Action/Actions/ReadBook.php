<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\DoesNotHaveMageBookSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReadBook extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::READ_BOOK;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private AddSkillToPlayerService $addSkillToPlayer,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new Mechanic([
                'mechanic' => EquipmentMechanicEnum::BOOK,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new PlaceType([
                'type' => PlaceTypeEnum::PLANET,
                'allowIfTypeMatches' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::ON_PLANET,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_READ_MAGE_BOOK,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::MAGE_BOOK_ALREADY_HAVE_READ,
            ]),
            new DoesNotHaveMageBookSkill([
                'groups' => [ClassConstraint::EXECUTE],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($this->bookMechanic()->isMageBook()) {
            $this->destroyBook();
            $this->addSkillToPlayer();
            $this->createLearnedSkillLog();
            $this->createHasReadMageBookStatus();
        }
    }

    private function destroyBook(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            equipment: $this->gameEquipmentTarget(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function addSkillToPlayer(): void
    {
        $this->addSkillToPlayer->execute($this->bookMechanic()->getSkill(), $this->player);
    }

    private function createLearnedSkillLog(): void
    {
        $learnedSkill = $this->bookMechanic()->getSkill();
        $this->roomLogService->createLog(
            logKey: LogEnum::LEARNED_SKILL,
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $this->player,
            parameters: [
                LogParameterKeyEnum::SKILL => $learnedSkill->toString(),
            ],
        );
    }

    private function createHasReadMageBookStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_READ_MAGE_BOOK,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function bookMechanic(): Book
    {
        return $this->gameEquipmentTarget()->getBookMechanicOrThrow();
    }
}
