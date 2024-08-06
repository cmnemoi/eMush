<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Learn extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::LEARN;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected ActionServiceInterface $actionService,
        protected ValidatorInterface $validator,
        private AddSkillToPlayerService $addSkillToPlayer,
    ) {
        parent::__construct($eventService, $actionService, $validator);
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
        $this->addSkillToPlayer->execute($this->skillToLearn(), $this->player);
    }

    private function skillToLearn(): SkillEnum
    {
        $params = $this->getParameters();
        if (!$params || !\array_key_exists('skill', $params)) {
            throw new \InvalidArgumentException('You need to select a skill to learn it!');
        }

        return SkillEnum::from($params['skill']);
    }
}
