<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Game\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepository;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CycleServiceCest extends AbstractExplorationTester
{
    private CycleServiceInterface $cycleService;
    private RebelBaseRepository $rebelBaseRepository;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->cycleService = $I->grabService(CycleServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepository::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // setup
        $this->statusService->createStatusFromName(
            statusName: 'rebel_base_contact_duration',
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->daedalus->getDaedalusInfo()->setGameStatus('in_game');
    }

    public function testHandleCycleChangeTriggerNewExplorationStep(FunctionalTester $I): void
    {
        // given Daedalus is in game so cycle changes can happen
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($this->daedalus);

        // given an exploration is in progress
        $exploration = $this->createExploration(
            planet: $this->createPlanet(
                sectors: [PlanetSectorEnum::OXYGEN, PlanetSectorEnum::HYDROCARBON],
                functionalTester: $I
            ),
            explorators: new PlayerCollection([$this->player]),
        );

        // given exploration is in its first step
        $I->assertEquals(1, $exploration->getCycle());

        // when I handle cycle change after a regular cycle duration
        $explorationStepDurationInMinutes = $exploration->getCycleLength() + 1;
        $newDateTime = clone $exploration->getCreatedAt();
        $newDateTime->modify("+{$explorationStepDurationInMinutes} minutes");

        $this->cycleService->handleDaedalusAndExplorationCycleChanges(
            dateTime: $newDateTime,
            daedalus: $this->daedalus,
        );

        // then the exploration should have advanced one step
        $I->assertEquals(2, $exploration->getCycle());
    }

    public function shouldTriggerRebelBaseContact(FunctionalTester $I): void
    {
        $this->givenRebelBasesExist([RebelBaseEnum::WOLF, RebelBaseEnum::SIRIUS], $I);

        // given Wolf is contacting
        $wolf = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), RebelBaseEnum::WOLF);
        $wolf->triggerContact();
        $this->rebelBaseRepository->save($wolf);

        // when one day worth of cycles pass
        $oneDayLater = clone $this->daedalus->getCycleStartedAtOrThrow();
        $oneDayLater->modify('+1 day');
        $this->cycleService->handleDaedalusAndExplorationCycleChanges(
            dateTime: $oneDayLater,
            daedalus: $this->daedalus,
        );

        // then sirius is contacting
        $sirius = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), RebelBaseEnum::SIRIUS);
        $I->assertTrue($sirius->isContacting(), 'Sirius should be contacting');

        // and contact start date should be exactly one day later
        $I->assertEquals(
            $oneDayLater,
            $sirius->getContactStartDateOrThrow(),
            "Sirius contact start date should be {$oneDayLater->format('Y-m-d H:i:s')}, got {$sirius->getContactStartDateOrThrow()->format('Y-m-d H:i:s')}"
        );
    }

    private function givenRebelBasesExist(array $rebelBases, FunctionalTester $I): void
    {
        foreach ($rebelBases as $rebelBase) {
            $kaladaanConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBase]);
            $this->rebelBaseRepository->save(
                new RebelBase($kaladaanConfig, $this->daedalus->getId())
            );
        }
    }
}
