<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Listener\PlayerStatistics;

use Mush\Action\Actions\Consume;
use Mush\Action\Actions\Daunt;
use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\DecodeRebelSignalService;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionPointsStatisticCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Move $move;
    private ActionConfig $moveConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->move = $I->grabService(Move::class);
        $this->moveConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]);

        $this->givenLaboratoryIsLinkedToMedlab($I);
        $this->givenDaedalusHasIcarusBay($I);
    }

    public function shouldUpdateTargetedActionReducingAnotherPlayerActionPoints(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::INTIMIDATING, $I, $this->kuanTi);

        $this->whenKuanTiIntimidatesChun($I); // 1 AP cost

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 1, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 0, $I);

        $this->thenPlayerShouldHaveActionPointsUsed($this->chun, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->chun, 0, $I);
    }

    public function shouldUpdateModifiedToIncreaseCostMushAction(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush($I);
        $this->givenCompleteProject(ProjectName::CONSTIPASPORE_SERUM, $I);

        $this->whenKuanTiExtractsASpore($I); // 2 AP base + 2 AP modified cost

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 2, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 2, $I);
    }

    public function shouldUpdateModifiedToIncreaseCostMushActionButWithSkillPoint(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush($I);
        $this->addSkillToPlayer(SkillEnum::FERTILE, $I, $this->kuanTi); // given spore skill point
        $this->givenCompleteProject(ProjectName::CONSTIPASPORE_SERUM, $I);

        $this->whenKuanTiExtractsASpore($I);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 2, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 0, $I);
    }

    public function shouldWasteActionPointSpentForMovement(FunctionalTester $I): void
    {
        $this->givenKuanTiHasNoMovementPoints();

        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 1, $I);
    }

    public function shouldWasteMoreActionPointsSpentForMovement(FunctionalTester $I): void
    {
        $this->givenKuanTiHasNoMovementPoints();
        $this->givenKuanTiHasStatus(PlayerStatusEnum::DISABLED);
        $this->givenKuanTiHasStatus(PlayerStatusEnum::BURDENED);

        // 2 AP cost = 1 MP base + 2 MP burdened - 1 MP disabled with Chun in room (conversion rate: 1 AP to 1 MP)
        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 2, $I);
    }

    public function shouldWasteActionPointsOnModifiedConsumptionGain(FunctionalTester $I): void
    {
        $this->givenSiriusRebelBaseIsDecoded($I);
        $ration = $this->givenKuanTiHasItem(ItemEnum::STANDARD_RATION);
        $this->givenKuanTiHasActionPoints(10);

        $this->whenKuanTiEats($ration, $I); // +5 AP

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 3, $I);
    }

    public function shouldWasteActionPointsOnCycleChange(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->chun);
        $this->givenKuanTiHasActionPoints(11);

        $this->whenCycleAdvances($I);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 1, $I);
    }

    public function shouldWasteActionPointsWhenFullOnCycleChange(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->chun);
        $this->givenKuanTiHasActionPoints(12);

        $this->whenCycleAdvances($I);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 2, $I);
    }

    public function shouldNotIncrementStatisticsOnFreeAction(FunctionalTester $I)
    {
        $this->addSkillToPlayer(SkillEnum::OBSERVANT, $I, $this->kuanTi);

        $this->whenKuanTiSearchesTheRoom($I);

        $this->thenPlayerShouldHaveActionPointsUsed($this->kuanTi, 0, $I);
        $this->thenPlayerShouldHaveActionPointsWasted($this->kuanTi, 0, $I);
    }

    private function givenLaboratoryIsLinkedToMedlab(FunctionalTester $I): void
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);

        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = Door::createFromRooms($laboratory, $medlab)->setEquipment($doorConfig);
        $I->haveInRepository($door);
    }

    private function givenDaedalusHasIcarusBay(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
    }

    private function givenKuanTiIsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function givenCompleteProject(ProjectName $projectName, FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName($projectName),
            author: $this->chun,
            I: $I
        );
    }

    private function givenKuanTiHasNoMovementPoints(): void
    {
        $this->kuanTi->setMovementPoint(0);
    }

    private function givenKuanTiHasStatus(string $statusName): void
    {
        $this->statusService->createStatusFromName(
            statusName: $statusName,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenSiriusRebelBaseIsDecoded(FunctionalTester $I): void
    {
        $rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);

        $siriusConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::SIRIUS]);
        $siriusRebelBase = new RebelBase(config: $siriusConfig, daedalusId: $this->daedalus->getId());
        $rebelBaseRepository->save($siriusRebelBase);

        $decodeRebelBase->execute(
            rebelBase: $siriusRebelBase,
            progress: 100,
        );
    }

    private function givenKuanTiHasItem(string $itemName): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasActionPoints(int $quantity): void
    {
        $this->kuanTi->setActionPoint($quantity);
    }

    private function whenKuanTiIntimidatesChun(FunctionalTester $I): void
    {
        $daunt = $I->grabService(Daunt::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DAUNT]);

        $daunt->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
        $daunt->execute();
    }

    private function whenKuanTiExtractsASpore(FunctionalTester $I): void
    {
        $extractASpore = $I->grabService(ExtractSpore::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXTRACT_SPORE]);

        $extractASpore->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $extractASpore->execute();
    }

    private function whenKuanTiMovesTo(string $placeName): void
    {
        $door = $this->kuanTi
            ->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->kuanTi->getPlace())->getName() === $placeName)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->move->execute();
    }

    private function whenKuanTiEats(GameItem $food, FunctionalTester $I): void
    {
        $consume = $I->grabService(Consume::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONSUME]);

        $consume->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $food,
            player: $this->kuanTi,
            target: $food,
        );
        $consume->execute();
    }

    private function whenCycleAdvances(): void
    {
        $event = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($event, DaedalusEvent::DAEDALUS_NEW_CYCLE);
    }

    private function whenKuanTiSearchesTheRoom(FunctionalTester $I): void
    {
        $search = $I->grabService(Search::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH]);

        $search->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $search->execute();
    }

    private function thenPlayerShouldHaveActionPointsUsed(Player $player, int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getPlayerInfo()->getStatistics()->getActionPointsUsed());
    }

    private function thenPlayerShouldHaveActionPointsWasted(Player $player, int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getPlayerInfo()->getStatistics()->getActionPointsWasted());
    }
}
