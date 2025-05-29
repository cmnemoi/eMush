<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TitleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    #[DataProvider('titleProvider')]
    public function shouldCreateHasGainedTitleStatus(FunctionalTester $I, Example $example): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->player,
                tags: [$example['title']],
                time: new \DateTime(),
            ),
            name: PlayerEvent::TITLE_ATTRIBUTED,
        );

        $I->assertTrue($this->player->hasStatus($example['status']));
    }

    private function titleProvider(): iterable
    {
        return [
            [
                'title' => TitleEnum::COMMANDER,
                'status' => PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE,
            ],
            [
                'title' => TitleEnum::COM_MANAGER,
                'status' => PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE,
            ],
            [
                'title' => TitleEnum::NERON_MANAGER,
                'status' => PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE,
            ],
        ];
    }
}
