<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\SkillMushEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hit extends Action
{
    protected const NAME = ActionEnum::HIT;

    private Player $target;
    private int $chanceSuccess;
    private int $damage;

    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        parent::__construct($eventDispatcher);

        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! ($target = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid target parameter');
        }

        $this->player  = $player;
        $this->target  = $target;
        $this->chanceSuccess = 50;
        $this->damage=0;
    }

    public function canExecute(): bool
    {
        return ($this->player->getRoom()===$this->target->getRoom() &&
            $this->player!==$this->target);
    }

    protected function applyEffects(): ActionResult
    {
        // @TODO: add knife case
        if ($this->randomService->random(0, 100)< $this->chanceSuccess) {
        } else {
            $this->damage = $this->randomService->random(1, 3);

            if (in_array(SkillEnum::SOLID, $this->player->getSkills())) {
                $this->damage=$this->damage+1;
            }
            if (in_array(SkillEnum::WRESTLER, $this->player->getSkills())) {
                $this->damage=$this->damage+2;
            }
            if (in_array(SkillMushEnum::HARD_BOILED, $this->target->getSkills())) {
                $this->damage=$this->damage-1;
            }
            if ($this->target->hasItemByName(ItemEnum::PLASTENITE_ARMOR)) {
                $this->damage=$this->damage-1;
            }
            if ($this->damage<=0) {
                // TODO:
            } elseif ($this->target->getHealthPoint()> $this->damage) {
                $this->target->setHealthPoint($this->target->getHealthPoint() - $this->damage);

                $this->playerService->persist($this->target);
            } else {
                // @TODO: kill the target
            }
        }
        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::HIT,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }


    public function getActionName(): string
    {
        return self::NAME;
    }
}
