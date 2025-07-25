<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class FearCats extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::FEAR_OF_CATS;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService
    ) {
        $this->randomService = $randomService;
        $this->eventService = $eventService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        $this->makePlayerRandomlyMoving($player);
    }

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): void
    {
        // get non-broken doors
        $availableDoors = $player->getPlace()->getDoors()->filter(static function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        if (\count($availableDoors) === 0) {
            return;
        }

        /** @var Door $randomDoor */
        $randomDoor = $this->randomService->getRandomElement($availableDoors);

        /** @var ActionConfig $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions($player, ActionHolderEnum::EQUIPMENT)->filter(static function (ActionConfig $action) {
            return $action->getActionName() === ActionEnum::MOVE;
        })->first();

        $moveEventAction = new ActionEvent(
            actionConfig: $moveActionEntity,
            actionProvider: $randomDoor,
            player: $player,
            tags: $moveActionEntity->getActionTags(),
            actionTarget: $randomDoor
        );
        $this->eventService->callEvent($moveEventAction, ActionEvent::EXECUTE_ACTION);
    }
}
