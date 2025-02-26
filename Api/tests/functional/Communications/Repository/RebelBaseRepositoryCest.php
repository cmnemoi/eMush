<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Repository;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RebelBaseRepositoryCest extends AbstractFunctionalTest
{
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
    }

    public function findNextContactingRebelBaseShouldReturnOnlyNonEmittingRebelBases(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);
        $this->givenRebelBaseIsAlreadyContacting(RebelBaseEnum::WOLF);

        $rebelBase = $this->rebelBaseRepository->findNextContactingRebelBase($this->daedalus->getId());

        $I->assertEquals(RebelBaseEnum::KALADAAN, $rebelBase->getName());
    }

    public function findNextContactingRebelBaseShouldReturnOnlyNonLostRebelBases(FunctionalTester $I): void
    {
        $this->givenRebelBasesExists([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);
        $this->givenRebelBaseContactAlreadyEnded(RebelBaseEnum::WOLF);

        $rebelBase = $this->rebelBaseRepository->findNextContactingRebelBase($this->daedalus->getId());

        $I->assertEquals(RebelBaseEnum::KALADAAN, $rebelBase->getName());
    }

    private function givenRebelBasesExists(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId()));
        }
    }

    private function givenRebelBaseIsAlreadyContacting(RebelBaseEnum $rebelBaseName): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $rebelBaseName);
        $rebelBase->triggerContact();
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenRebelBaseContactAlreadyEnded(RebelBaseEnum $rebelBaseName): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $rebelBaseName);
        $rebelBase->endContact();
        $this->rebelBaseRepository->save($rebelBase);
    }
}
