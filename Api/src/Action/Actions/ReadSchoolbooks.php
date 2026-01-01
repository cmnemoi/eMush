<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasSkill;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillConfigRepositoryInterface;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReadSchoolbooks extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::READ_SCHOOLBOOKS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private AddSkillToPlayerService $addSkillToPlayer,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
        private DeletePlayerSkillService $deletePlayerSkill,
        private readonly SkillConfigRepositoryInterface $skillConfigRepository,
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
            new PlaceType([
                'type' => PlaceTypeEnum::PLANET,
                'allowIfTypeMatches' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::ON_PLANET,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_READ_SCHOOLBOOKS_ANNIVERSARY,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasSkill([
                'skill' => SkillEnum::POLYVALENT,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DOES_NOT_HAVE_SKILL_POLYVALENT,
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
        $this->destroyBook();
        $this->createHasReadSchoolbooksStatus();
        $this->deletePolyvalentSkillFromPlayer();
        $this->addBotanistBiologistDiplomat();
        $this->createRevisedLog();
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

    private function deletePolyvalentSkillFromPlayer(): void
    {
        $this->player->removeFromAvailableHumanSkills($this->player->getSkillByNameOrThrow(SkillEnum::POLYVALENT)->getConfig());
        $this->deletePlayerSkill->execute(skillName: SkillEnum::POLYVALENT, player: $this->player);
    }

    private function addBotanistBiologistDiplomat(): void
    {
        $this->addSkillToPlayer(SkillEnum::BOTANIST);
        $this->addSkillToPlayer(SkillEnum::BIOLOGIST);
        $this->addSkillToPlayer(SkillEnum::DIPLOMAT);
    }

    private function addSkillToPlayer(SkillEnum $skill): void
    {
        $this->addSkillToPlayer->execute($skill, $this->player, tags: $this->getTags());
    }

    private function createRevisedLog(): void
    {
        $this->roomLogService->createLog(
            logKey: LogEnum::REVISED_KNOWLEDGE,
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $this->player,
            parameters: [],
        );
    }

    private function createHasReadSchoolbooksStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_READ_SCHOOLBOOKS_ANNIVERSARY,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
