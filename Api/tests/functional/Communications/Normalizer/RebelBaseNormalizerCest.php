<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Normalizer;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Normalizer\RebelBaseNormalizer;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RebelBaseNormalizerCest extends AbstractFunctionalTest
{
    private RebelBaseNormalizer $rebelBaseNormalizer;
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->rebelBaseNormalizer = $I->grabService(RebelBaseNormalizer::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
    }

    public function shouldNormalizeInactiveRebelBase(FunctionalTester $I): void
    {
        $rebelBase = new RebelBase(
            config: $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => 'wolf_default']),
            daedalusId: $this->daedalus->getId()
        );

        $normalized = $this->rebelBaseNormalizer->normalize($rebelBase, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals([
            'key' => 'wolf',
            'name' => '???',
            'hoverName' => '???',
            'description' => 'Cette base rebelle semble inactive pour le moment. À un moment du voyage, elle enverra un signal à décoder. Assurez-vous qu\'il soit intercepté pour bénéficier de ses avantages.',
            'signal' => '0%',
            'isContacting' => false,
            'isLost' => false,
        ], $normalized);
    }

    public function shouldNormalizeLostRebelBase(FunctionalTester $I): void
    {
        $rebelBase = new RebelBase(
            config: $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => 'wolf_default']),
            daedalusId: $this->daedalus->getId()
        );
        $rebelBase->endContact();

        $normalized = $this->rebelBaseNormalizer->normalize($rebelBase, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals([
            'key' => 'wolf',
            'name' => '???',
            'hoverName' => '???',
            'description' => 'Cette base rebelle a lancé un signal mais personne n\'a pu le décoder. Les apports de cette base sont perdus à jamais.',
            'signal' => '0%',
            'isContacting' => false,
            'isLost' => true,
        ], $normalized);
    }

    public function shouldNormalizeContactingRebelBase(FunctionalTester $I): void
    {
        $rebelBase = new RebelBase(
            config: $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => 'wolf_default']),
            daedalusId: $this->daedalus->getId()
        );
        $this->rebelBaseRepository->save($rebelBase);
        $rebelBase->triggerContact();
        $rebelBase->increaseDecodingProgress(50);

        $normalized = $this->rebelBaseNormalizer->normalize($rebelBase, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals([
            'key' => 'wolf',
            'name' => '???',
            'hoverName' => '???',
            'description' => 'Cette base rebelle a lancé un signal. En le décodant, vous obtiendrez des informations précieuses voir même des avantages pour l\'équipage. Si vous tardez à le traiter, ce signal sera perdu à jamais.',
            'signal' => '50%',
            'isContacting' => true,
            'isLost' => false,
        ], $normalized);
    }

    public function shouldNormalizeDecodedRebelBase(FunctionalTester $I): void
    {
        $rebelBase = new RebelBase(
            config: $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => 'wolf_default']),
            daedalusId: $this->daedalus->getId()
        );
        $this->rebelBaseRepository->save($rebelBase);
        $rebelBase->triggerContact();
        $rebelBase->increaseDecodingProgress(100);
        $rebelBase->endContact();

        $normalized = $this->rebelBaseNormalizer->normalize($rebelBase, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals([
            'key' => 'wolf',
            'name' => 'Wolf',
            'hoverName' => 'Message des Enfants Perdus de Wolf',
            'description' => 'Les enfants perdus de Wolf ont installé plusieurs antennes sur Terre ainsi qu\'un centre d’émission à Mumbaï. Nous allons pouvoir émettre sur les ondes radios terriennes les nouvelles de votre odyssée. Vous êtes des héros.',
            'signal' => '100%',
            'isContacting' => false,
            'isLost' => false,
        ], $normalized);
    }
}
