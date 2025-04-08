<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Normalizer;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Normalizer\XylophEntryNormalizer;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class XylophEntryNormalizerCest extends AbstractFunctionalTest
{
    private XylophEntryNormalizer $normalizer;
    private XylophRepositoryInterface $xylophRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(XylophEntryNormalizer::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
    }

    #[DataProvider('xylophEntryDataProvider')]
    public function shouldNormalizeXylophEntry(FunctionalTester $I, Example $data): void
    {
        $xylophEntryConfig = $I->grabEntityFromRepository(XylophConfig::class, ['name' => $data['key']]);
        $xylophEntry = new XylophEntry($xylophEntryConfig, $this->daedalus->getId(), $data['isDecoded']);
        $this->xylophRepository->save($xylophEntry);

        $normalizedEntry = $this->normalizer->normalize($xylophEntry, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals(
            expected: [
                'key' => $data['key'],
                'name' => $data['name'],
                'description' => $data['description'],
                'isDecoded' => $data['isDecoded'],
                'updatedAt' => $xylophEntry->getUpdatedAtOrThrow()->format('Y-m-d H:i:s'),
            ],
            actual: $normalizedEntry
        );
    }

    private function xylophEntryDataProvider(): array
    {
        return [
            [
                'key' => 'magnetite',
                'name' => '???',
                'description' => '???',
                'isDecoded' => false,
            ],
            [
                'key' => 'disk',
                'name' => 'Génome',
                'description' => 'Ce disque regroupe des informations cruciales sur le génome Mush rassemblées par Ian Soulton juste avant le départ du Daedalus.//**Fournit la disquette contenant l\'analyse du génome Mush.**',
                'isDecoded' => true,
            ],
            [
                'key' => 'nothing',
                'name' => 'Donnée corrompue',
                'description' => 'Rien à en tirer... La donnée a été corrompue par un virus rhizhomal.//**Ne fait rien.**',
                'isDecoded' => true,
            ],
            [
                'key' => 'magnetite',
                'name' => 'Infidélité du sans-fil...',
                'description' => 'La liaison est de mauvaise qualité...très mauvaise, sans doute le nuage de magnetite que vous traversez en ce moment.//**Provoque une perte de liaison et une baisse de signal significative.**',
                'isDecoded' => true,
            ],
            [
                'key' => 'snow',
                'name' => 'De la neige...',
                'description' => 'Votre écran est couvert de neige grésillante, ça fait mal au crâne, vous arrêtez vos tentatives...//**Provoque une perte de liaison.**',
                'isDecoded' => true,
            ],
            [
                'key' => 'version',
                'name' => 'Accès à la base de données perdue de NERON',
                'description' => 'Une partie des travaux de recherche de Janice sur Xyloph-17 sont intégrés à la mémoire de NERON.//**Fait avancer le niveau de mise à jour de NERON de 25%.**',
                'isDecoded' => true,
            ],
        ];
    }
}
