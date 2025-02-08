<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\InMemoryLinkWithSolRepository;
use Mush\Communications\Service\EstablishLinkWithSolService;
use Mush\Daedalus\Factory\DaedalusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EstablishLinkWithSolServiceTest extends TestCase
{
    private InMemoryLinkWithSolRepository $linkWithSolRepository;

    /**
     * @before
     */
    public function before(): void
    {
        $this->linkWithSolRepository = new InMemoryLinkWithSolRepository();
    }

    public function testShouldIncreaseLinkStrengthByGivenValue(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->linkWithSolRepository->save(
            new LinkWithSol(strength: 0, isEstablished: false, daedalusId: $daedalus->getId())
        );

        $establishLinkWithSol = new EstablishLinkWithSolService($this->linkWithSolRepository);
        $establishLinkWithSol->execute(daedalusId: $daedalus->getId(), strengthIncrease: 10);

        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalus->getId());
        self::assertSame(10, $linkWithSol->getStrength());
    }

    public function testShouldMarkLinkAsEstablished(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $this->linkWithSolRepository->save(
            new LinkWithSol(strength: 0, isEstablished: false, daedalusId: $daedalus->getId())
        );

        $establishLinkWithSol = new EstablishLinkWithSolService($this->linkWithSolRepository);
        $establishLinkWithSol->execute(daedalusId: $daedalus->getId(), strengthIncrease: 10);

        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalus->getId());
        self::assertTrue($linkWithSol->isEstablished());
    }
}
