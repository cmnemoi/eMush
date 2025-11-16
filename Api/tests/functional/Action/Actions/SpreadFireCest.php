<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\SpreadFire;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SpreadFireCest extends AbstractFunctionalTest
{
    private ActionConfig $spreadFireConfig;
    private SpreadFire $spreadFire;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->spreadFireConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SPREAD_FIRE]);
        $this->spreadFire = $I->grabService(SpreadFire::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->addSkillToPlayer(SkillEnum::PYROMANIAC, $I, $this->kuanTi);
    }

    public function shouldProduceAWorkingFire(FunctionalTester $I): void
    {
        $this->kuanTi->setHealthPoint(6);

        $this->whenKuanTiStartsFire();

        $this->whenANewCycleHappen();

        $I->assertTrue($this->kuanTi->getPlace()->hasStatus(StatusEnum::FIRE), 'Room should be burning.');
        $I->assertEquals(4, $this->kuanTi->getHealthPoint(), 'Player should have lost 2HP.');
    }

    public function fireShouldBeAbleToSpreadImmediatly(FunctionalTester $I): void
    {
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setPropagatingFireRate(100);

        // given there is two rooms connected to each other
        $place1 = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $place2 = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $door = Door::createFromRooms($place1, $place2);
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        // given Kuan Ti is in room 1
        $this->kuanTi->setPlace($place1);

        $this->whenKuanTiStartsFire();

        $this->whenANewCycleHappen();

        $I->assertTrue($place2->hasStatus(StatusEnum::FIRE), 'Room should be burning.');
    }

    public function shouldGeneratePlayerHighlight(FunctionalTester $I): void
    {
        $this->whenKuanTiStartsFire();

        $this->thenKuanTiHasPlayerHighlight($I);
    }

    private function whenKuanTiStartsFire(): void
    {
        $this->spreadFire->loadParameters(
            actionConfig: $this->spreadFireConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->spreadFire->execute();
    }

    private function whenANewCycleHappen(): void
    {
        $event = new DaedalusCycleEvent(
            $this->kuanTi->getDaedalus(),
            [EventEnum::NEW_CYCLE],
            $this->player->getDaedalus()->getCycleStartedAtOrThrow()
        );

        $this->eventService->callEvent(
            $event,
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE
        );
    }

    private function thenKuanTiHasPlayerHighlight(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: [
                'name' => 'spread_fire',
                'result' => PlayerHighlight::SUCCESS,
                'parameters' => [
                    'target_place' => $this->kuanTi->getPlace()->getLogName(),
                ],
            ],
            actual: $this->kuanTi->getPlayerInfo()->getPlayerHighlights()[0]->toArray(),
        );
    }
}
