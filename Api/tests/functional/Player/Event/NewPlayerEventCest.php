<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NewPlayerEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldCreateWelcomeOnBoardPopup(FunctionalTester $I): void
    {
        $event = new PlayerEvent($this->player, [], new \DateTime());
        $event->setCharacterConfig($this->player->getCharacterConfig());

        $this->eventService->callEvent(
            $event,
            PlayerEvent::NEW_PLAYER
        );

        $I->seeInRepository(
            entity: PlayerNotification::class,
            params: [
                'player' => $this->player,
                'message' => PlayerNotificationEnum::WELCOME_ON_BOARD,
            ]
        );
    }

    public function shouldCreateRestartGravityNeronMessageOnFirstPlayerWakeUp(FunctionalTester $I): void
    {
        $event = new PlayerEvent($this->player, [PlayerEvent::FIRST_PLAYER_ON_BOARD], new \DateTime());
        $event->setCharacterConfig($this->player->getCharacterConfig());

        $this->eventService->callEvent(
            $event,
            PlayerEvent::NEW_PLAYER
        );

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::RESTART_GRAVITY,
            ]
        );
    }

    public function shouldNotCreateRestartGravityNeronMessageOnSecondPlayerWakeUp(FunctionalTester $I): void
    {
        $event = new PlayerEvent($this->player, [], new \DateTime());
        $event->setCharacterConfig($this->player->getCharacterConfig());

        $this->eventService->callEvent(
            $event,
            PlayerEvent::NEW_PLAYER
        );

        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::RESTART_GRAVITY,
            ]
        );
    }
}
