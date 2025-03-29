<?php

declare(strict_types=1);

namespace Mush\Action\Tests\Functional\Actions;

use Mush\Action\Actions\AcceptTrade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Factory\TradeFactory;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Exception\GameException;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class AcceptTradeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private AcceptTrade $acceptTrade;

    private CreateHunterService $createHunter;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private HunterRepositoryInterface $hunterRepository;
    private StatusServiceInterface $statusService;
    private TradeRepositoryInterface $tradeRepository;

    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ACCEPT_TRADE]);
        $this->acceptTrade = $I->grabService(AcceptTrade::class);

        $this->createHunter = $I->grabService(CreateHunterService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->hunterRepository = $I->grabService(HunterRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->tradeRepository = $I->grabService(TradeRepositoryInterface::class);

        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->givenStorages($I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfHuntersAreAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->givenHunterIsAttackingDaedalus(HunterEnum::HUNTER);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotTheAliveCommsManager(FunctionalTester $I): void
    {
        $this->givenOtherPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::COMS_NOT_OFFICER);
    }

    public function shouldBeVisibleIfAsteroidIsAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenHunterIsAttackingDaedalus(HunterEnum::ASTEROID);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeVisible($I);
    }

    public function shouldBeVisibleIfTransportIsAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenHunterIsAttackingDaedalus(HunterEnum::TRANSPORT);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeVisible($I);
    }

    public function shouldThrowExceptionIfTradeTermsAreNotMet(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);

        $I->expectThrowable(GameException::class, function () use ($trade) {
            $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());
        });
    }

    public function shouldBeExecutableIfThereIsNoAliveCommsManager(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeExecutable($I);
    }

    public function shouldConsumeRequiredTradeOptionAssets(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $frontStorage = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_STORAGE);
        $I->assertFalse($frontStorage->hasEquipmentByName(ItemEnum::HYDROPOT), 'Hydropot in front storage should be consumed');
    }

    public function shouldGiveOfferedTradeOptionAssets(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertCount(10, $this->player->getPlace()->getAllEquipmentsByName(ItemEnum::OXYGEN_CAPSULE), 'Player room should have 10 oxygen capsules');
    }

    public function shouldCreatePublicLogForCreatedItems(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Une cargaison de 10 **Capsules d\'oxygène** a été téléportée sur le pont ! Sympa la technologie alien !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::TRADE_ASSETS_CREATED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldDeleteTransport(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->expectThrowable(new \RuntimeException("Hunter not found for id {$trade->getTransportId()}"), function () use ($trade) {
            $this->hunterRepository->findByIdOrThrow($trade->getTransportId());
        });
    }

    public function shouldDeleteTrade(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertEmpty($this->tradeRepository->findAllByDaedalusId($this->daedalus->getId()));
    }

    public function shouldCreateMerchantLeaveNeronAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_LEAVE,
            ],
        );
    }

    public function shouldCreateMerchantExchangeNeronAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_EXCHANGE,
            ],
        );
    }

    private function givenStorages(FunctionalTester $I): void
    {
        foreach (RoomEnum::getStorages() as $storage) {
            $this->createExtraPlace($storage, $I, $this->daedalus);
        }
    }

    private function givenHunterIsAttackingDaedalus(string $hunter): void
    {
        $this->createHunter->execute($hunter, $this->daedalus->getId());
    }

    private function givenPlayerIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }

    private function givenPlayerIsCommsManager(): void
    {
        $this->player->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenOtherPlayerIsCommsManager(): void
    {
        $this->player2->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenForestDealTrade(int $requiredHydropot, int $offeredOxygen): Trade
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createForestDealTrade(
            requiredHydropot: $requiredHydropot,
            offeredOxygen: $offeredOxygen,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenItemInPlace(string $item, string $room): void
    {
        $place = $this->daedalus->getPlaceByNameOrThrow($room);
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $item,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToAcceptTrade(): void
    {
        $this->acceptTrade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
    }

    private function whenPlayerAcceptsTrade(int $tradeOptionId): void
    {
        $this->acceptTrade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
            parameters: [
                'tradeOptionId' => $tradeOptionId,
            ],
        );

        $this->acceptTrade->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->acceptTrade->isVisible(), 'Action should not be visible');
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $cause): void
    {
        $I->assertEquals($cause, $this->acceptTrade->cannotExecuteReason(), "Action should not be executable with cause: {$cause}");
    }

    private function thenActionShouldBeVisible(FunctionalTester $I): void
    {
        $I->assertTrue($this->acceptTrade->isVisible(), 'Action should be visible');
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->acceptTrade->cannotExecuteReason(), 'Action should be executable');
    }
}
