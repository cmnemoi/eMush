<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Listener;

use Mush\Action\Actions\Examine;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InfectPlayerCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    private ActionConfig $actionConfig;
    private Examine $action;

    private Player $frieda;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::EXAMINE]);
        $this->action = $this->action = $I->grabService(Examine::class);

        $this->frieda = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FRIEDA);
    }

    public function shouldDistributeTriumphOnConversion(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setSpores(2);
        $this->kuanTi->setTriumph(0);
        $this->frieda->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertTrue($this->frieda->hasStatus(PlayerStatusEnum::MUSH));
        // Conversion triumph
        $I->assertEquals(8, $this->kuanTi->getTriumph());
        // "Late" convert triumph
        $I->assertEquals(120, $this->frieda->getTriumph());
    }

    public function shouldDeadAuthorGainTriumphOnConversion(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->givenRoomIsTrappedByKuanTi();
        $this->givenKuanTiIsDead();
        $this->frieda->setSpores(2);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertTrue($this->frieda->hasStatus(PlayerStatusEnum::MUSH));
        // Conversion triumph
        $I->assertEquals(8, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldHumanAuthorNotGainTriumphOnConversion(FunctionalTester $I): void
    {
        $this->givenRoomIsTrappedByKuanTi();
        $this->givenKuanTiIsDead();
        $this->frieda->setSpores(2);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertTrue($this->frieda->hasStatus(PlayerStatusEnum::MUSH));

        $I->assertEquals(0, $this->kuanTi->getTriumph());
    }

    public function shouldConvertedHumanGetReducedTriumphForLateConversion(FunctionalTester $I): void
    {
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setSpores(2);
        $this->givenDaedalusIsRegisteredAsFull();
        $this->frieda->setTriumph(0);

        $this->whenXCyclesPass(7);
        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertTrue($this->frieda->hasStatus(PlayerStatusEnum::MUSH));

        $I->assertEquals(106, $this->frieda->getTriumph()); // (7 human cycle triumph) + 120 + (-3) * 7
    }

    public function shouldDistributeTriumphOnInfection(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        // Infection triumph
        $I->assertEquals(1, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->frieda->getTriumph());
    }

    public function shouldDeadAuthorGainMushTriumphOnInfection(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        // Infection triumph
        $I->assertEquals(1, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->frieda->getTriumph());
    }

    public function shouldHumanAuthorNotGainMushTriumphOnInfection(FunctionalTester $I): void
    {
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->frieda->getTriumph());
    }

    public function shouldNotGainMushTriumphWhenSporeIsWasted(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $this->frieda);
        $this->givenRoomIsTrappedByKuanTi();
        $this->frieda->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->whenFriedaInteractsWithRoomEquipment();

        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->frieda->getTriumph());
    }

    private function givenRoomIsTrappedByKuanTi(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->frieda->getPlace(),
            tags: [],
            time: new \DateTime(),
            target: $this->kuanTi,
        );
    }

    private function givenKuanTiIsDead(): void
    {
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::DEPRESSION,
        );
    }

    private function givenDaedalusIsRegisteredAsFull(): void
    {
        $this->daedalus->setFilledAt(new \DateTime());
    }

    private function whenFriedaInteractsWithRoomEquipment(): void
    {
        $equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->frieda->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        $this->action->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $equipment,
            player: $this->frieda,
            target: $equipment
        );
        $this->action->execute();
    }

    private function whenXCyclesPass(int $x): void
    {
        $time = $this->daedalus->getCycleStartedAtOrThrow();
        $daedalusConfig = $this->daedalus->getGameConfig()->getDaedalusConfig();
        $interval = new \DateInterval('PT' . $daedalusConfig->getCycleLength() . 'M');
        for ($i = 0; $i < $x; ++$i) {
            $time->add($interval);
            $this->eventService->callEvent(
                new DaedalusCycleEvent($this->daedalus, tags: [EventEnum::NEW_CYCLE], time: $time),
                DaedalusCycleEvent::DAEDALUS_NEW_CYCLE
            );
            $this->daedalus->setCycleStartedAt($time);
        }
    }
}
