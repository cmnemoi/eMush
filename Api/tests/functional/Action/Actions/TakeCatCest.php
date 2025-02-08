<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\TakeCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TakeCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private TakeCat $takeCat;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE_CAT->value]);
        $this->takeCat = $I->grabService(TakeCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCatIsInShelf($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->whenPlayerTakesCat();
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => ActionLogEnum::TAKE_CAT,
            ]
        );
    }

    public function shouldInfectHumanIfCatIsInfected(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->actionConfig->setInjuryRate(100);

        $this->whenPlayerTakesCat();

        $this->thenPlayerShouldHaveSpores(1, $I);
    }

    public function shouldNotInfectMushPlayer(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->givenPlayerIsMush();

        $this->actionConfig->setInjuryRate(100);

        $this->whenPlayerTakesCat();

        $this->thenPlayerShouldHaveSpores(0, $I);
    }

    public function shouldPrintLogInMushChannelWhenInfectingPlayer(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->actionConfig->setInjuryRate(100);

        $this->whenPlayerTakesCat();

        $I->seeInRepository(
            Message::class,
            [
                'channel' => $this->mushChannel,
                'message' => MushMessageEnum::INFECT_CAT,
            ]
        );
    }

    private function givenCatIsInShelf(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenCatIsInfected(FunctionalTester $I): void
    {
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $jinSu,
            tags: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $this->schrodinger,
            tags: [],
            time: new \DateTime(),
            target: $jinSu,
        );
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function whenPlayerTriesToTakeCat(): void
    {
        $this->takeCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->schrodinger,
            player: $this->player,
            target: $this->schrodinger,
        );
    }

    private function whenPlayerTakesCat(): void
    {
        $this->whenPlayerTriesToTakeCat();
        $this->takeCat->execute();
    }

    private function thenPlayerShouldHaveSpores(int $expectedSpores, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSpores, $this->player->getSpores());
    }
}
