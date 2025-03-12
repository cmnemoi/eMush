<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Service\KillAllRebelBaseContactsService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryRebelBaseRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class KillRebelBaseContactsServiceTest extends TestCase
{
    private KillAllRebelBaseContactsService $killAllRebelBaseContacts;
    private InMemoryRebelBaseRepository $rebelBaseRepository;
    private Daedalus $daedalus;

    protected function setUp(): void
    {
        $this->rebelBaseRepository = new InMemoryRebelBaseRepository();

        $this->killAllRebelBaseContacts = new KillAllRebelBaseContactsService(
            $this->rebelBaseRepository,
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    public function testShouldMarkAllRebelBaseContactsAsFinished(): void
    {
        $this->givenTwoContactingRebelBases();

        $this->whenKillingAllContactingRebelBases();

        $this->thenAllRebelBasesContactsShouldHaveEnded();
    }

    public function testShouldNotMarkNonInitiatedContactsAsFinished(): void
    {
        $this->givenOneNonContactingRebelBase();

        $this->whenKillingAllContactingRebelBases();

        $this->thenTheRebelBaseShouldNotHaveEndedContact();
    }

    private function givenTwoContactingRebelBases(): void
    {
        $this->createContactingRebelBase();
        $this->createContactingRebelBase();
    }

    private function givenOneNonContactingRebelBase(): void
    {
        $this->createNonContactingRebelBase();
    }

    private function createContactingRebelBase(): RebelBase
    {
        $rebelBase = new RebelBase(
            config: RebelBaseConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
            contactStartDate: new \DateTimeImmutable(),
        );
        $this->rebelBaseRepository->save($rebelBase);

        return $rebelBase;
    }

    private function createNonContactingRebelBase(): RebelBase
    {
        $rebelBase = new RebelBase(
            config: RebelBaseConfig::createNull(),
            daedalusId: $this->daedalus->getId(),
        );
        $this->rebelBaseRepository->save($rebelBase);

        return $rebelBase;
    }

    private function whenKillingAllContactingRebelBases(): void
    {
        $this->killAllRebelBaseContacts->execute($this->daedalus->getId());
    }

    private function thenAllRebelBasesContactsShouldHaveEnded(): void
    {
        $rebelBases = $this->rebelBaseRepository->findAllByDaedalusId($this->daedalus->getId());

        foreach ($rebelBases as $rebelBase) {
            self::assertTrue($rebelBase->contactEnded());
        }
    }

    private function thenTheRebelBaseShouldNotHaveEndedContact(): void
    {
        $rebelBases = $this->rebelBaseRepository->findAllByDaedalusId($this->daedalus->getId());

        foreach ($rebelBases as $rebelBase) {
            self::assertFalse($rebelBase->contactEnded());
        }
    }
}
