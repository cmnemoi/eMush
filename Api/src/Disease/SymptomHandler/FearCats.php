<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FearCats extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::FEAR_OF_CATS;
    private ActionServiceInterface $actionService;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private ValidatorInterface $validator;

    public function __construct(
        ActionServiceInterface $actionService,
        EventServiceInterface $eventService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        ValidatorInterface $validator,
    ) {
        $this->actionService = $actionService;
        $this->eventService = $eventService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->validator = $validator;
    }

    public function applyEffects(string $symptomName, Player $player, \DateTime $time): void
    {
        if ($symptomName !== SymptomEnum::FEAR_OF_CATS) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->makePlayerRandomlyMoving($player);
    }

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): void
    {
        // get non broken doors
        $availaibleDoors = $player->getPlace()->getDoors()->filter(function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        if (count($availaibleDoors) === 0) {
            return;
        }

        // get random door
        $selectedDoor = $this->randomService->getRandomElements($availaibleDoors, 1);
        $randomDoor = reset($selectedDoor);

        /** @var Action $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions()->filter(function (Action $action) {
            return $action->getActionName() === ActionEnum::MOVE;
        })->first();

        $moveAction = new Move(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService
        );
        $moveAction->loadParameters($moveActionEntity, $player, $randomDoor);
        $moveAction->execute();
    }
}
