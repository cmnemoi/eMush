<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractSpore extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::EXTRACT_SPORE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusService $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::DAEDALUS,
            'checkMode' => GameVariableLevel::IS_MAX,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT,
        ]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'checkMode' => GameVariableLevel::IS_MAX,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::PERSONAL_SPORE_LIMIT,
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->incrementPlayerSpores();
        $this->incrementDaedalusSpores();

        if ($this->player->hasSkill(SkillEnum::FERTILE)) {
            $this->markThatPlayerUsedFertileToday();
        }
    }

    private function incrementPlayerSpores(): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            1,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function incrementDaedalusSpores(): void
    {
        $daedalusModifierEvent = new DaedalusVariableEvent(
            $this->player->getDaedalus(),
            DaedalusVariableEnum::SPORE,
            1,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($daedalusModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function markThatPlayerUsedFertileToday(): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::HAS_USED_FERTILE_TODAY,
            $this->player,
            $this->getTags(),
            new \DateTime(),
        );
    }
}
