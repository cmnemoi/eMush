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
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
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

        foreach (RoomEnum::getStorages() as $storage) {
            $this->createExtraPlace($storage, $I, $this->daedalus);
        }
    }

    public function shouldNormalizeHumanVsOxyTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        // Given a HUMAN_VS_OXY trade
        $trade = $this->givenHumanVsOxyTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Aloha, vos organismes sont vraiment passionnants surtout la métanisation de vos déchets organiques, incroyable... Nous vous échangeons un de vos amis contre des réserves d\'oxygène conséquentes !',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 équipier au hasard vous rapportera... 10 capsules d\'oxygène.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] Ça tombe bien, on a du rabe sur les déchets organiques...',
                    'description' => 'Vendre 2 équipiers au hasard vous rapportera... 24 capsules d\'oxygène.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizeForestDealTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        // Given a FOREST_DEAL trade
        $trade = $this->givenForestDealTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'OOOOOOOOoooooo...... *rahh mes senseurs* Nous sommes des ...*chhhhh* Jardiniers spaciaux, nous échangerons volontiers votre matériel contre des biens précieux.',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 hydropot vous rapportera... 10 capsules d\'oxygène, 2 capsules de fuel.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()[1]->getId(),
                    'name' => '[Diplomatie] Vendre plus !',
                    'description' => 'Vendre 2 hydropots vous rapportera... 15 capsules d\'oxygène, 3 capsules de fuel.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] Votre diplomate vous pond un charabia incompréhensible qui semble ravir les marchands !',
                    'description' => 'Vendre 1 hydropot, 1 micro-ondes, 1 blaster vous rapportera... 12 capsules d\'oxygène.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizePilgredissimTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        // Given a PILGREDISSIM trade
        $trade = $this->givenPilgredissimTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            '[NAc Nac NAc NAc] *amélioration du faisceau* [Nac] Moteur pourri, jamais avancer dans l\'univers comme ça. Civilisation en carton... [Naaaaac] Réparons votre PILGRED contre de vos membres d\'équipage !',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 3 équipiers au hasard vous rapportera... 1 réparation du PILGRED.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] Tapis !',
                    'description' => 'Vendre 24 unités d\'oxygène, 24 unités de fuel vous rapportera... 1 réparation du PILGRED.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizeGoodProjectionsTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        // Given a GOOD_PROJECTIONS trade
        $trade = $this->givenGoodProjectionsTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Salutation humble créature mammalienne, nous recherchons de nouveaux jouets pour satisfaire notre reine. Nous disposons de technologies qui pourraient vous être utiles.',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 équipier au hasard vous rapportera... 1 projet au hasard.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()[1]->getId(),
                    'name' => '[Diplomatie] Allez ! Vous en prendrez bien un deuxième ?',
                    'description' => 'Vendre 2 équipiers au hasard vous rapportera... 2 projets au hasard.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] Et sinon... Vous acceptez les paiements en nature ?',
                    'description' => 'Vendre 5 rations standard, 5 débris métalliques, 1 débris plastique, 5 unités d\'oxygène vous rapportera... 1 projet au hasard.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizeTechnoRewriteTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        // Given a TECHNO_REWRITE trade
        $trade = $this->givenTechnoRewriteTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Les grandes maisons de Serres vous saluent, nous regardons votre monde primitif depuis des eons et la folle course de votre vaisseau est intriguante. Nous avons quand même quelques intérêts pour certaine de vos technologies, un petit échange pourrait profiter à tous.',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 2 projets au hasard vous rapportera... 1 projet au hasard.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] En fait, on vous fait un prix de groupe !',
                    'description' => 'Vendre 3 projets au hasard vous rapportera... 2 projets au hasard.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizeHumanVsFuelTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);
        $this->givenKuanTiIsABotanist($I);

        // Given a HUMAN_VS_FUEL trade
        $trade = $this->givenHumanVsFuelTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Nous avons un surplus d\'antigel et il nous manque à manger pour ce soir, ça vous dit ?',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 équipier au hasard vous rapportera... 10 capsules de fuel.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()[1]->getId(),
                    'name' => '[Diplomatie] On vous fait un prix de gros, ça vous dit ?',
                    'description' => 'Vendre 2 équipiers au hasard vous rapportera... 20 capsules de fuel.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Botaniste] Digérez-vous bien la verdure ?',
                    'description' => 'Vendre 4 rations standard vous rapportera... 3 capsules de fuel.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNormalizeHumanVsTreeTrade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsADiplomat($I);

        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        // Given a HUMAN_VS_TREE trade
        $trade = $this->givenHumanVsTreeTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Buraroum, nous, les Dévorfeuilles avons besoin de jardinier céleste, nous vous échangeons de la main d\'oeuvre contre des pots neufs.',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 équipier au hasard vous rapportera... 1 hydropot.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()[1]->getId(),
                    'name' => '[Diplomatie] On a exactement ce qu\'il vous faut !',
                    'description' => 'Vendre Ian Soulton vous rapportera... 4 hydropots, 4 capsules d\'oxygène.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
                [
                    'id' => $trade->getTradeOptions()->last()->getId(),
                    'name' => '[Diplomatie] Et sinon ça vous intéresse ?',
                    'description' => 'Vendre 2 équipiers au hasard vous rapportera... 3 hydropots.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    public function shouldNotNormalizeDiplomatTradeOptionsIfNoDiplomatInTheRoom(FunctionalTester $I): void
    {
        // Given a HUMAN_VS_TREE trade
        $trade = $this->givenHumanVsTreeTrade($I);

        // When normalizing the trade
        $normalizedTrade = $this->whenNormalizingTrade($trade);

        // Then the normalized trade should have the expected structure
        $this->thenTradeIsNormalizedCorrectly(
            $I,
            $trade,
            $normalizedTrade,
            'Buraroum, nous, les Dévorfeuilles avons besoin de jardinier céleste, nous vous échangeons de la main d\'oeuvre contre des pots neufs.',
            [
                [
                    'id' => $trade->getTradeOptions()->first()->getId(),
                    'name' => 'Vendu !',
                    'description' => 'Vendre 1 équipier au hasard vous rapportera... 1 hydropot.',
                    'tradeConditionsAreNotMet' => 'Les conditions de cet échange ne sont pas remplies.',
                ],
            ]
        );
    }

    // Helper methods for test setup (Given)
    private function givenHumanVsOxyTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::HUMAN_VS_OXY,
            tradeOptions: [
                new TradeOption(
                    name: 'human_vs_oxy_1_random_player_vs_5-10_oxygen_capsules',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 1,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                            quantity: 10,
                        ),
                    ],
                ),
                new TradeOption(
                    name: 'human_vs_oxy_diplomat_2_random_players_vs_10-20_oxygen_capsules',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                            quantity: 24,
                        ),
                    ],
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenForestDealTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::FOREST_DEAL,
            tradeOptions: [
                new TradeOption(
                    name: 'forest_deal_1_hydropot_vs_8-12_oxygen_capsules_1-4_fuel_capsules',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 10,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 2,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'forest_deal_2_hydropot_vs_12-20_oxygen_capsules_3-4_fuel_capsules',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 2,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 15,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 3,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'forest_deal_1_hydropot_plus_optional_items_vs_12_oxygen_capsules_optional_lunchbox',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ToolItemEnum::MICROWAVE,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ItemEnum::BLASTER,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 12,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenPilgredissimTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::PILGREDISSIM,
            tradeOptions: [
                new TradeOption(
                    name: 'pilgredissim_3_random_players_vs_pilgred_project',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 3,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::SPECIFIC_PROJECT,
                            quantity: 1,
                            assetName: ProjectName::PILGRED->toString(),
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'pilgredissim_diplomat_24_oxygen_24_fuel_vs_pilgred_project',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::DAEDALUS_VARIABLE,
                            assetName: DaedalusVariableEnum::OXYGEN,
                            quantity: 24,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::DAEDALUS_VARIABLE,
                            assetName: DaedalusVariableEnum::FUEL,
                            quantity: 24,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::SPECIFIC_PROJECT,
                            quantity: 1,
                            assetName: ProjectName::PILGRED->toString(),
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenGoodProjectionsTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::GOOD_PROJECTIONS,
            tradeOptions: [
                new TradeOption(
                    name: 'good_projections_one_random_player_vs_one_random_project',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 1,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 1,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'good_projections_diplomat_two_random_players_vs_two_random_projects',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 2,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'good_projections_diplomat_mixed_resources_vs_one_random_project',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 5,
                            assetName: GameRationEnum::STANDARD_RATION,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 5,
                            assetName: ItemEnum::METAL_SCRAPS,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ItemEnum::PLASTIC_SCRAPS,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::DAEDALUS_VARIABLE,
                            assetName: DaedalusVariableEnum::OXYGEN,
                            quantity: 5,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 1,
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenTechnoRewriteTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::TECHNO_REWRITE,
            tradeOptions: [
                new TradeOption(
                    name: 'techno_rewrite_two_random_projects_vs_one_random_project',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 1,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'techno_rewrite_diplomat_three_random_projects_vs_two_random_projects',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 3,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PROJECT,
                            quantity: 2,
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenHumanVsFuelTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::HUMAN_VS_FUEL,
            tradeOptions: [
                new TradeOption(
                    name: 'human_vs_fuel_1_random_player_vs_8-12_fuel_capsules',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 1,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 10,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'human_vs_fuel_diplomat_2_random_players_vs_10-30_fuel_capsules',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 20,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'human_vs_fuel_botanist_4_rations_vs_2-4_fuel_capsules',
                    requiredSkill: SkillEnum::BOTANIST,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 4,
                            assetName: GameRationEnum::STANDARD_RATION,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 3,
                            assetName: ItemEnum::FUEL_CAPSULE,
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenHumanVsTreeTrade(FunctionalTester $I): Trade
    {
        $trade = new Trade(
            name: TradeEnum::HUMAN_VS_TREE,
            tradeOptions: [
                new TradeOption(
                    name: 'human_vs_tree_1_random_player_vs_one_hydropot',
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 1,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 1,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'human_vs_tree_diplomat_ian_vs_4_hydropots_4_oxygen_capsules',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::SPECIFIC_PLAYER,
                            quantity: 1,
                            assetName: CharacterEnum::IAN,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 4,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 4,
                            assetName: ItemEnum::OXYGEN_CAPSULE,
                        ),
                    ]
                ),
                new TradeOption(
                    name: 'human_vs_tree_diplomat_2_random_players_vs_3_hydropots',
                    requiredSkill: SkillEnum::DIPLOMAT,
                    requiredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::RANDOM_PLAYER,
                            quantity: 2,
                        ),
                    ],
                    offeredAssets: [
                        new TradeAsset(
                            type: TradeAssetEnum::ITEM,
                            quantity: 3,
                            assetName: ItemEnum::HYDROPOT,
                        ),
                    ]
                ),
            ],
            transportId: $this->createTransport($I)->getId()
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function whenNormalizingTrade(Trade $trade): array
    {
        return $this->tradeNormalizer->normalize($trade, format: null, context: ['currentPlayer' => $this->player]);
    }

    private function thenTradeIsNormalizedCorrectly(
        FunctionalTester $I,
        Trade $trade,
        array $normalizedTrade,
        string $expectedDescription,
        array $expectedOptions
    ): void {
        $I->assertEquals(
            expected: [
                'id' => $trade->getId(),
                'description' => $expectedDescription,
                'options' => $expectedOptions,
                'image' => $trade->getName()->toImageId(),
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

    private function givenKuanTiIsADiplomat(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::DIPLOMAT, $I, $this->kuanTi);
    }

    private function givenKuanTiIsABotanist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::BOTANIST, $I, $this->kuanTi);
    }
}
