<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Repository;

use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class XylophRepositoryCest extends AbstractFunctionalTest
{
    private XylophRepositoryInterface $xylophRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
    }

    public function testFindAllUndecodedXyloph(FunctionalTester $I): void
    {
        $this->givenXylophExists([XylophEnum::DISK, XylophEnum::SNOW, XylophEnum::NOTHING], $I);
        $this->givenXylophIsDecoded(XylophEnum::DISK);
        $this->thenUndecodedXylophDatabaseShouldBe([XylophEnum::SNOW, XylophEnum::NOTHING], $I);
    }

    public function testAllXylophShouldBeDecoded(FunctionalTester $I): void
    {
        $this->givenXylophExists([XylophEnum::DISK, XylophEnum::SNOW], $I);
        $this->givenXylophIsDecoded(XylophEnum::DISK);
        $this->givenXylophIsDecoded(XylophEnum::SNOW);
        $this->thenAllXylophShouldBeDecoded($I);
    }

    private function givenXylophExists(array $xylophNames, FunctionalTester $I): void
    {
        foreach ($xylophNames as $xylophName) {
            $config = $I->grabEntityFromRepository(XylophConfig::class, ['name' => $xylophName]);
            $this->xylophRepository->save(new XylophEntry($config, $this->daedalus->getId()));
        }
    }

    private function givenXylophIsDecoded(XylophEnum $xylophEnum): void
    {
        $xylophEntry = $this->xylophRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $xylophEnum->value);
        $xylophEntry->unlockDatabase();
        $this->xylophRepository->save($xylophEntry);
    }

    private function thenUndecodedXylophDatabaseShouldBe(array $xylophArray, FunctionalTester $I): void
    {
        $undecodedXyloph = $this->xylophRepository->findAllUndecodedByDaedalusId($this->daedalus->getId());
        $undecodedXylophArray = array_map(static fn (XylophEntry $xylophEntry) => $xylophEntry->getName(), $undecodedXyloph);

        $I->assertEqualsCanonicalizing($xylophArray, $undecodedXylophArray);
    }

    private function thenAllXylophShouldBeDecoded(FunctionalTester $I): void
    {
        $xylophDecoded = $this->xylophRepository->areAllXylophDatabasesDecoded($this->daedalus->getId());

        $I->assertTrue($xylophDecoded);
    }
}
