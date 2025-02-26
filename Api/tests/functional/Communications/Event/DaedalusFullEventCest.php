<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusFullEventCest extends AbstractFunctionalTest
{
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;
    private EventServiceInterface $eventService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);

        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
    }

    public function shouldMakeFirstRebelBaseContactingTheDaedalus(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);

        $this->whenDaedalusIsFull();

        $this->thenRebelBaseShouldContact(RebelBaseEnum::WOLF, $I);
        $this->thenRebelBaseShouldNotContact(RebelBaseEnum::KALADAAN, $I);
    }

    private function givenRebelBasesExists(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId()));
        }
    }

    private function whenDaedalusIsFull(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusEvent(daedalus: $this->daedalus, tags: [], time: new \DateTime()),
            name: DaedalusEvent::FULL_DAEDALUS,
        );
    }

    private function thenRebelBaseShouldContact(RebelBaseEnum $rebelBaseName, FunctionalTester $I): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $rebelBaseName);

        $I->assertFalse($rebelBase->isNotContacting(), "Rebel base {$rebelBaseName->toString()} should be contacting the daedalus");
    }

    private function thenRebelBaseShouldNotContact(RebelBaseEnum $rebelBaseName, FunctionalTester $I): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $rebelBaseName);

        $I->assertTrue($rebelBase->isNotContacting(), "Rebel base {$rebelBaseName->toString()} should not be contacting the daedalus");
    }
}
