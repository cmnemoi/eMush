<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Game\Service\RandomServiceInterface;


class Hit extends Action
{
    private Player $player;
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! ($target = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid target parameter');
        }

        $this->player  = $player;
        $this->target  = $target;
	$this->cost_pa = 1;
	$this->chance_success = 50;
    }

    public function canExecute(): bool
    {
        return $this->player->getActionPoint() > $this->cost_pa && $this->player->getRoom()===$this->target->getRoom();
    }

    protected function apply(): ActionResult
    {
        
        $this->player->setActionPoint($this->player->getActionPoint() - $this->cost_pa);
            
        $this->playerService->persist($this->player);

	if (random(0, 100)< $this->chance_success){
		// TODO: add log
	} else {
		$this->damage = random(1, 3);

		if (in_array('solid', $this->player->getSkills())){
			$this->damage=$this->damage+1;
		}
		if (in_array('wrestler', $this->player->getSkills())){
			$this->damage=$this->damage+2;
		}
		if (in_array('hard_boiled', $this->target->getSkills())){
			$this->damage=$this->damage-1;
		}
		if($player->hasItemByName(ItemEnum::PLASTENITE_ARMOR)) {
			$this->damage=$this->damage-1;
		}
		if ($this->damage<=0) {
			// TODO:
		} elseif ($this->target->getHealthPoint()>damage) {
			$this->target->setHealthPoint($this->target->getHealthPoint() - $this->damage);
            
        	$this->playerService->persist($this->target);
			
		} else {
			// TODO: kill the target
		}
	}

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        // TODO: Implement createLog() method.
    }
}
