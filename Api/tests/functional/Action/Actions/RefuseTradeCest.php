<?php

declare(strict_types=1);

namespace Mush\Action\Tests\Functional\Actions;

use Mush\Action\Actions\RefuseTrade;
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
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RefuseTradeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private RefuseTrade $refuseTrade;

    private CreateHunterService $createHunter;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private HunterRepositoryInterface $hunterRepository;
    private StatusServiceInterface $statusService;
    private TradeRepositoryInterface $tradeRepository;

    private GameEquipment $commsCenter;
    private Trade $trade;
    private Hunter $transport;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REFUSE_TRADE]);
        $this->refuseTrade = $I->grabService(RefuseTrade::class);

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
        [$this->trade, $this->transport] = $this->givenTradeAndTransport();
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToRefuseTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfHuntersAreAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenHunterIsAttackingDaedalus();

        $this->whenPlayerTriesToRefuseTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotTheAliveCommsManager(FunctionalTester $I): void
    {
        $this->givenOtherPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToRefuseTrade();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::COMS_NOT_OFFICER, $I);
    }

    public function shouldDeleteTransport(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerRefusesTrade();

        $this->thenTransportShouldBeDeleted($I);
    }

    public function shouldDeleteTrade(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerRefusesTrade();

        $this->thenTradeShouldBeDeleted($I);
    }

    public function shouldNotCreateMerchantLeaveNeronAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerRefusesTrade();

        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_LEAVE,
            ],
        );
    }

    public function shouldNotGiveTriumphOnRefuseTrade(FunctionalTester $I): void
    {
        $andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerRefusesTrade();

        $I->assertEquals(0, $andie->getTriumph());
        $I->assertEquals(0, $this->player->getTriumph());
    }

    private function givenTradeAndTransport(): array
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createForestDealTrade(
            requiredHydropot: 0,
            offeredOxygen: 0,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return [$trade, $transport];
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

    private function givenHunterIsAttackingDaedalus(): void
    {
        $this->createHunter->execute(HunterEnum::HUNTER, $this->daedalus->getId());
    }

    private function givenOtherPlayerIsCommsManager(): void
    {
        $this->player2->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenPlayerIsCommsManager(): void
    {
        $this->player->addTitle(TitleEnum::COM_MANAGER);
    }

    private function whenPlayerTriesToRefuseTrade(): void
    {
        $this->refuseTrade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
            parameters: [
                'tradeId' => $this->trade->getId(),
            ],
        );
    }

    private function whenPlayerRefusesTrade(): void
    {
        $this->whenPlayerTriesToRefuseTrade();
        $this->refuseTrade->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->refuseTrade->isVisible(), 'Action should not be visible');
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->refuseTrade->cannotExecuteReason());
    }

    private function thenTransportShouldBeDeleted(FunctionalTester $I): void
    {
        $I->expectThrowable(new \RuntimeException("Hunter not found for id {$this->trade->getTransportId()}"), function () {
            $this->hunterRepository->findByIdOrThrow($this->trade->getTransportId());
        });
    }

    private function thenTradeShouldBeDeleted(FunctionalTester $I): void
    {
        $I->assertEmpty($this->tradeRepository->findAllByDaedalusId($this->daedalus->getId()));
    }
}
