<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Learn extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::LEARN;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected ActionServiceInterface $actionService,
        protected ValidatorInterface $validator,
        private AddSkillToPlayerService $addSkillToPlayer,
        private DeletePlayerSkillService $deletePlayerSkill,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new NumberPlayersAliveInRoom([
                'mode' => NumberPlayersAliveInRoom::LESS_THAN,
                'number' => 2,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::LONELY_APPRENTICESHIP,
            ])
        );
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
        $this->checkSkillToLearnIsInTheRoom();
        $this->checkSkillToLearnIsNotAMushSkill();

        $this->addLearnedSkillToPlayer();
        $this->deleteApprenticeSkillFromPlayer();
        $this->createHasLearnedSkillStatus();
    }

    private function checkSkillToLearnIsInTheRoom(): void
    {
        $playersInRoom = $this->player->getPlace()->getAlivePlayersExcept($this->player);

        foreach ($playersInRoom as $player) {
            if ($player->hasSkill($this->skillToLearn())) {
                return;
            }
        }

        throw new GameException('There is no player with this skill in the room!');
    }

    private function checkSkillToLearnIsNotAMushSkill(): void
    {
        if ($this->skillToLearn()->isMushSkill()) {
            throw new GameException('You cannot learn a Mush skill!');
        }
    }

    private function addLearnedSkillToPlayer(): void
    {
        $this->addSkillToPlayer->execute(skill: $this->skillToLearn(), player: $this->player);
    }

    private function deleteApprenticeSkillFromPlayer(): void
    {
        $this->deletePlayerSkill->execute(skill: SkillEnum::APPRENTICE, player: $this->player);
    }

    private function createHasLearnedSkillStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_LEARNED_SKILL,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function skillToLearn(): SkillEnum
    {
        $params = $this->getParameters();
        if (!$params || !\array_key_exists('skill', $params)) {
            throw new GameException('You need to select a skill to learn it!');
        }

        return SkillEnum::from($params['skill']);
    }
}
