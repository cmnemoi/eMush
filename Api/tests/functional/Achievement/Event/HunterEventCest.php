<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterEventCest extends AbstractFunctionalTest
{
    private AlertServiceInterface $alertService;
    private CreateHunterService $createHunter;
    private EventServiceInterface $eventService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->alertService = $I->grabService(AlertServiceInterface::class);
        $this->createHunter = $I->grabService(CreateHunterService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    #[DataProvider('hostileHuntersDataProvider')]
    public function shouldIncrementHunterDownStatisticForHostileHunters(FunctionalTester $I, Example $example): void
    {
        $this->createHunter->execute($example[0], $this->daedalus->getId());
        $this->alertService->handleHunterArrival($this->daedalus);

        $this->eventService->callEvent(
            event: new HunterEvent(
                hunter: $I->grabEntityFromRepository(Hunter::class),
                visibility: VisibilityEnum::HIDDEN,
                tags: [ActionEnum::SHOOT_HUNTER->value], // add a tag so kill is properly logged
                time: new \DateTime(),
            )->setAuthor($this->chun),
            name: HunterEvent::HUNTER_DEATH,
        );

        $I->assertEquals(
            expected: 1,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::HUNTER_DOWN, $this->chun->getUser()->getId())?->getCount(),
            message: "HunterDown statistic should be incremented for {$example[0]}"
        );
    }

    #[DataProvider('nonHostileHuntersDataProvider')]
    public function shouldNotIncrementHunterDownStatisticForNonHostileHunters(FunctionalTester $I, Example $example): void
    {
        $this->createHunter->execute($example[0], $this->daedalus->getId());
        $this->alertService->handleHunterArrival($this->daedalus);

        $this->eventService->callEvent(
            event: new HunterEvent(
                hunter: $I->grabEntityFromRepository(Hunter::class),
                visibility: VisibilityEnum::HIDDEN,
                tags: [ActionEnum::SHOOT_HUNTER->value], // add a tag so kill is properly logged
                time: new \DateTime(),
            )->setAuthor($this->chun),
            name: HunterEvent::HUNTER_DEATH,
        );

        $I->assertNull(
            $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::HUNTER_DOWN, $this->chun->getUser()->getId()),
            message: "HunterDown statistic should not be incremented for {$example[0]}"
        );
    }

    public static function hostileHuntersDataProvider(): array
    {
        return HunterEnum::getHostiles()->map(static fn (string $hunterName) => [$hunterName])->toArray();
    }

    public static function nonHostileHuntersDataProvider(): array
    {
        return HunterEnum::getNonHostiles()->map(static fn (string $hunterName) => [$hunterName])->toArray();
    }
}
