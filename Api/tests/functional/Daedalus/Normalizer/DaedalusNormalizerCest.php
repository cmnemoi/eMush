<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Normalizer;

use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class DaedalusNormalizerCest extends AbstractFunctionalTest
{
    private DaedalusNormalizer $normalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(DaedalusNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
    }

    public function shouldNotNormalizeShieldIfPlasmaShieldProjectIsNotFinished(FunctionalTester $I): void
    {
        // given PlasmaShield is not finished (default)

        // when I normalize the daedalus
        $normalizedDaedalus = $this->normalizer->normalize($this->daedalus);

        $I->assertNull($normalizedDaedalus['shield']);
    }

    public function shouldNormalizeShieldIfPlasmaShieldProjectIsFinished(FunctionalTester $I): void
    {
        // given PlasmaShield is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            $this->chun,
            $I
        );

        // when I normalize the daedalus
        $normalizedDaedalus = $this->normalizer->normalize($this->daedalus);

        $I->assertEquals(
            expected: [
                'quantity' => 50,
                'name' => 'Bouclier plasma : 50',
                'description' => "L'état de l'armure vous indique l'état global de la coque du vaisseau. À **0 le vaisseau sera détruit** car la coque ne sera plus qu'un maigre paravent troué...",
            ],
            actual: $normalizedDaedalus['shield']
        );
    }
}