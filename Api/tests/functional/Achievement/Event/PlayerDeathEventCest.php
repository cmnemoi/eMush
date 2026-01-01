<?php

declare(strict_types=1);

namespace Mush\tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Shoot;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerDeathEventCest extends AbstractFunctionalTest
{
    private ActionConfig $shootConfig;
    private Shoot $shootAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->shootConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT]);
        $this->shootAction = $I->grabService(Shoot::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldNotGrantNatamistStatisticOnHumanKillHuman(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldNotHaveNatamistStatistic($I);
    }

    public function shouldGrantNatamistStatisticOnHumanKillMush(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();
        $this->givenPlayer2IsMush($I);

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldHaveNatamistStatistic($I);
    }

    public function shouldNotGrantNatamistStatisticOnMushKillMush(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();
        $this->givenPlayerIsMush($I);
        $this->givenPlayer2IsMush($I);

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldNotHaveNatamistStatistic($I);
    }

    private function givenPlayer2HasOneHealthPoint(): void
    {
        $this->player2->setHealthPoint(1);
    }

    private function givenPlayerHasNatamyRifle(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::NATAMY_RIFLE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player);
    }

    private function givenPlayer2IsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player2);
    }

    private function whenPlayerKillsPlayer2WithNatamyRifle(): void
    {
        /** @var GameItem $natamyRifle */
        $natamyRifle = $this->player->getEquipments()->filter(
            static fn (GameItem $item) => $item->getName() === ItemEnum::NATAMY_RIFLE
        )->first();
        $natamyRifle->getWeaponMechanicOrThrow()->setBaseAccuracy(100);

        $this->shootAction->loadParameters(
            actionConfig: $this->shootConfig,
            actionProvider: $natamyRifle,
            player: $this->player,
            target: $this->player2,
        );
        $this->shootAction->execute();
    }

    private function thenPlayerShouldHaveNatamistStatistic(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();
        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::NATAMIST,
                'count' => 1,
                'userId' => $this->player->getUser()->getId(),
                'closedDaedalusId' => $closedDaedalusId,
                'isRare' => false,
            ],
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::NATAMIST,
                $this->player->getUser()->getId(),
                $closedDaedalusId
            )?->toArray(),
            message: "{$this->player->getLogName()} should have natamist statistic"
        );
    }

    private function thenPlayerShouldNotHaveNatamistStatistic(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();
        $I->assertNull(
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::NATAMIST,
                $this->player->getUser()->getId(),
                $closedDaedalusId
            ),
            message: "{$this->player->getLogName()} should have NOT natamist statistic"
        );
    }
}
