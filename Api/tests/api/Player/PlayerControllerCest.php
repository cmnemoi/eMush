<?php

declare(strict_types=1);

namespace Mush\tests\api\Player;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Entity\PersonalNotesTab;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\ApiTester;
use Mush\User\Entity\User;

class PlayerControllerCest
{
    private DaedalusServiceInterface $daedalusService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;
    private User $user;
    private Daedalus $daedalus;
    private Player $player;

    public function _before(ApiTester $I): void
    {
        $this->user = $I->loginUser('default');
        $this->user->acceptRules();
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $this->givenDaedalusAndPlayerExist();
    }

    public function testUpdatePersonalNotesTabsSuccess(ApiTester $I): void
    {
        $tabs = [
            'tabs' => [
                [
                    'id' => $this->getFirstTabId(),
                    'index' => 0,
                    'icon' => 'notepad',
                    'content' => 'Updated content',
                ],
                [
                    'index' => 1,
                    'icon' => 'project',
                    'content' => 'New tab content',
                ],
            ],
        ];

        $this->whenIUpdatePersonalNotesTabs($I, $tabs);

        $this->thenResponseShouldContainTabs($I, true, $tabs['tabs']);
    }

    public function testUpdatePersonalNotesTabsRemovesOldTabs(ApiTester $I): void
    {
        $this->givenPlayerHasMultipleTabs();

        $tabs = [
            'tabs' => [
                [
                    'index' => 0,
                    'icon' => 'notepad',
                    'content' => 'Only this tab remains',
                ],
            ],
        ];
        $this->whenIUpdatePersonalNotesTabs($I, $tabs);

        $this->thenResponseShouldContainTabs($I, true, $tabs['tabs']);
    }

    public function testUpdatePersonalNotesTabsWithTooManyTabs(ApiTester $I): void
    {
        $tabs = [];
        for ($i = 0; $i < 17; ++$i) {
            $tabs[] = [
                'index' => $i,
                'icon' => 'notepad',
                'content' => 'Tab ' . $i,
            ];
        }

        $this->whenIUpdatePersonalNotesTabs($I, ['tabs' => $tabs]);

        $this->thenResponseShouldBeUnprocessable($I);
    }

    public function testUpdatePersonalNotesTabsWithContentTooLong(ApiTester $I): void
    {
        $longContent = str_repeat('a', 65537); // Exceeds max length of 65536

        $this->whenIUpdatePersonalNotesTabs($I, [
            'tabs' => [
                [
                    'index' => 0,
                    'icon' => 'notepad',
                    'content' => $longContent,
                ],
            ],
        ]);

        $this->thenResponseShouldBeUnprocessable($I);
    }

    public function testUpdatePersonalNotesTabsRequiresAuthentication(ApiTester $I): void
    {
        $this->givenUserIsNotAuthenticated($I);

        $this->whenIUpdatePersonalNotesTabs($I, [
            'tabs' => [
                [
                    'index' => 0,
                    'icon' => 'notepad',
                    'content' => 'content',
                ],
            ],
        ]);

        $this->thenResponseShouldBeUnauthorized($I);
    }

    public function testUpdatePersonalNotesTabsWithNonExistentTabId(ApiTester $I): void
    {
        $this->whenIUpdatePersonalNotesTabs($I, [
            'tabs' => [
                [
                    'id' => 999999,
                    'index' => 0,
                    'icon' => 'notepad',
                    'content' => 'content',
                ],
            ],
        ]);

        $this->thenResponseShouldBeNotFound($I);
    }

    public function testUpdatePersonalNotesTabsUpdatesExistingTab(ApiTester $I): void
    {
        $tabId = $this->getFirstTabId();

        $tabs = [
            'tabs' => [
                [
                    'id' => $tabId,
                    'index' => 0,
                    'icon' => 'project',
                    'content' => 'Modified content',
                ],
            ],
        ];
        $this->whenIUpdatePersonalNotesTabs($I, $tabs);

        $this->thenResponseShouldContainTabs($I, true, $tabs['tabs']);
    }

    private function givenDaedalusAndPlayerExist(): void
    {
        $this->daedalus = $this->daedalusService->createDaedalus($this->gameConfig, 'test_daedalus', LanguageEnum::FRENCH);
        $this->player = $this->playerService->createPlayer($this->daedalus, $this->user, CharacterEnum::ANDIE);
    }

    private function givenPlayerHasMultipleTabs(): void
    {
        $personalNotes = $this->player->getPersonalNotes();

        for ($i = 0; $i < 3; ++$i) {
            $tab = new PersonalNotesTab(
                $personalNotes,
                'notepad',
                'Tab ' . $i,
                $i + 1
            );
            $personalNotes->addTab($tab);
        }

        $this->playerService->persist($this->player);
    }

    private function givenUserIsNotAuthenticated(ApiTester $I): void
    {
        $I->amBearerAuthenticated('invalid_token');
    }

    private function whenIUpdatePersonalNotesTabs(ApiTester $I, array $data): void
    {
        $I->sendPutRequest('/player/' . $this->player->getId() . '/notes/tabs', $data, true);
    }

    private function thenResponseShouldBeUnprocessable(ApiTester $I): void
    {
        $I->seeResponseCodeIs(422);
    }

    private function thenResponseShouldBeUnauthorized(ApiTester $I): void
    {
        $I->seeResponseCodeIs(401);
    }

    private function thenResponseShouldBeNotFound(ApiTester $I): void
    {
        $I->seeResponseCodeIs(404);
    }

    private function thenResponseShouldContainTabs(ApiTester $I, bool $hasAccess, array $tabs): void
    {
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['hasAccess' => $hasAccess]);
        $I->seeResponseContainsJson(['tabs' => $tabs]);
    }

    private function getFirstTabId(): int
    {
        return $this->player->getPersonalNotes()->getTabs()->first()->getId();
    }
}
