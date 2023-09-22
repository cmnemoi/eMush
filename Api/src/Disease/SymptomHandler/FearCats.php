<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class FearCats extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::FEAR_OF_CATS;
    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $moveEvent = $this->makePlayerRandomlyMoving($player);

        if ($moveEvent !== null) {
            $moveEvent->setPriority($priority);

            return new EventChain([$moveEvent]);
        }

        return new EventChain([]);
    }

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): ?AbstractGameEvent
    {
        // get non broken doors
        $availaibleDoors = $player->getPlace()->getDoors()->filter(function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        if (count($availaibleDoors) === 0) {
            return null;
        }

        // get random door
        $selectedDoor = $this->randomService->getRandomElements($availaibleDoors, 1);
        $randomDoor = reset($selectedDoor);

        /** @var Action $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions()->filter(function (Action $action) {
            return $action->getActionName() === ActionEnum::MOVE;
        })->first();

        $moveEventAction = new ActionEvent(
            $moveActionEntity,
            $player,
            $randomDoor
        );
        $moveEventAction->setEventName(ActionEvent::EXECUTE_ACTION);

        return $moveEventAction;
    }
}
