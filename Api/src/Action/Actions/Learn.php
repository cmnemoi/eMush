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
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Skill\Service\DeletePlayerSkillService;
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
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $skillToLearn = $this->skillToLearn();

        $this->checkPlayerDoesNotHaveSkillToLearn($skillToLearn);
        $this->checkSkillToLearnIsInTheRoom($skillToLearn);
        $this->checkSkillToLearnIsNotAMushSkill($skillToLearn);

        $this->addSkillToPlayer->execute($skillToLearn, $this->player);
        $this->deletePlayerSkill->execute(SkillEnum::APPRENTICE, $this->player);
    }

    private function skillToLearn(): SkillEnum
    {
        $params = $this->getParameters();
        if (!$params || !\array_key_exists('skill', $params)) {
            throw new GameException('You need to select a skill to learn it!');
        }

        return SkillEnum::from($params['skill']);
    }

    private function checkPlayerDoesNotHaveSkillToLearn(SkillEnum $skillToLearn): void
    {
        if ($this->player->hasSkill($skillToLearn)) {
            throw new GameException('You already have this skill!');
        }
    }

    private function checkSkillToLearnIsInTheRoom(SkillEnum $skillToLearn): void
    {
        $playersInRoom = $this->player->getPlace()->getAlivePlayersExcept($this->player);

        foreach ($playersInRoom as $player) {
            if ($player->hasSkill($skillToLearn)) {
                return;
            }
        }

        throw new GameException('No player with this skill is in the room!');
    }

    private function checkSkillToLearnIsNotAMushSkill(SkillEnum $skillToLearn): void
    {
        if ($skillToLearn->isMushSkill()) {
            throw new GameException('You cannot learn a Mush skill!');
        }
    }
}
