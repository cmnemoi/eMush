<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\SkillMushEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;

    private Player $target;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        SuccessRateServiceInterface $successRateService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($randomService, $successRateService, $eventDispatcher, $statusService);

        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($target = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid target parameter');
        }

        $this->target = $target;
    }

    public function canExecute(): bool
    {
        return $this->player->getRoom() === $this->target->getRoom() &&
            $this->player !== $this->target;
    }

    protected function applyEffects(): ActionResult
    {
        $baseRate = 50;
        $modificator = 1; //@TODO
        $result = $this->makeAttempt($baseRate, $modificator);

        if ($result instanceof Success) {
            $damage = $this->randomService->random(1, 3);

            if (in_array(SkillEnum::SOLID, $this->player->getSkills())) {
                ++$damage;
            }
            if (in_array(SkillEnum::WRESTLER, $this->player->getSkills())) {
                $damage += 2;
            }
            if (in_array(SkillMushEnum::HARD_BOILED, $this->target->getSkills())) {
                --$damage;
            }
            if ($this->target->hasItemByName(GearItemEnum::PLASTENITE_ARMOR)) {
                --$damage;
            }
            if ($damage <= 0) {
                // TODO:
            } else {
                $actionModifier = new ActionModifier();
                $actionModifier->setHealthPointModifier(-$damage);

                $playerEvent = new PlayerEvent($this->target);
                $playerEvent->setActionModifier($actionModifier);
                $playerEvent->setReason(EndCauseEnum::ASSASSINATED);
                $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

                $this->playerService->persist($this->target);
            }
        }

        return new Success(ActionLogEnum::HIT_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
