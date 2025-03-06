<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Normalizer;

use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Enum\TradeAssetEnum;
use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Normalizer\TradeNormalizer;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class TradeNormalizerCest extends AbstractFunctionalTest
{
    private TradeNormalizer $tradeNormalizer;
    private TradeRepositoryInterface $tradeRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->tradeNormalizer = $I->grabService(TradeNormalizer::class);
        $this->tradeNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->tradeRepository = $I->grabService(TradeRepositoryInterface::class);
    }

    public function shouldNormalizeTrade(FunctionalTester $I): void
    {
        $trade = new Trade(
            name: TradeEnum::HUMAN_VS_OXY,
            tradeOptions: [
                new TradeOption(
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 1,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::DAEDALUS_VARIABLE,
                            assetName: DaedalusVariableEnum::OXYGEN,
                            quantity: 10,
                        ),
                    ],
                ),
                new TradeOption(
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::DAEDALUS_VARIABLE,
                            assetName: DaedalusVariableEnum::OXYGEN,
                            quantity: 24,
                        ),
                    ],
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        $normalizedTrade = $this->tradeNormalizer->normalize($trade, format: null, context: ['currentPlayer' => $this->player]);

        $I->assertEquals(
            expected: [
                'id' => $trade->getId(),
                'description' => 'Aloha, vos organismes sont vraiment passionnants surtout la métanisation de vos déchets organiques, incroyable... Nous vous échangeons un de vos amis contre des réserves d\'oxygène conséquentes !',
                'options' => [
                    [
                        'name' => 'Vendu !',
                        'description' => 'Vendre 1 équipier au hasard vous rapportera... 10 unités d\'oxygène.',
                    ],
                    [
                        'name' => '[Diplomatie] Vendu !',
                        'description' => 'Vendre 2 équipiers au hasard vous rapportera... 24 unités d\'oxygène.',
                    ],
                ],
            ],
            actual: $normalizedTrade
        );
    }

    private function createTransport(FunctionalTester $I): Hunter
    {
        $transport = new Hunter(
            hunterConfig: $I->grabEntityFromRepository(HunterConfig::class, ['name' => HunterEnum::TRANSPORT . '_default']),
            daedalus: $this->daedalus,
        );
        $I->haveInRepository($transport);

        return $transport;
    }
}
