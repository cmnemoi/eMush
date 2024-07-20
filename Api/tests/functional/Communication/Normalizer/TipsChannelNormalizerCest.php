<?php

declare(strict_types=1);

namespace Mush\tests\functional\Communication\Normalizer;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Normalizer\TipsChannelNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TipsChannelNormalizerCest extends AbstractFunctionalTest
{
    public TipsChannelNormalizer $normalizer;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(TipsChannelNormalizer::class);
    }

    public function shouldSupportNormaliaztionOnlyForChannel(FunctionalTester $I): void
    {
        $I->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function shouldNotSupportNormalizationForChannelOtherThanTips(FunctionalTester $I): void
    {
        $I->assertFalse($this->normalizer->supportsNormalization(Channel::createPublicChannel()));
    }

    #[DataProvider('shouldNormalizeChannelNameDescriptionAndTipsForPlayerDataProvider')]
    public function shouldNormalizeChannelNameDescriptionAndTipsForPlayer(FunctionalTester $I, Example $example): void
    {
        $player = $this->addPlayerByCharacter($I, $this->daedalus, $example['characterKey']);

        $channel = Channel::createTipsChannel();
        $channel->setDaedalus($this->daedalus->getDaedalusInfo());

        $I->assertEquals(
            expected: [
                'name' => 'Conseils',
                'description' => 'Trouvez ici des **conseils pratiques** pour la vie quotidienne (sponsorisé par mangerbouger.fr).',
                'tips' => [
                    'teamObjectives' => [
                        'title' => $example['teamObjectivesTitle'],
                        'elements' => [
                            '**Éliminer les Mushs à bord**.',
                            '**Réparer le PILGRED** pour rentrer sur Sol.',
                            '**Coopérer avec les autres humains** pour survivre à l\'aide de vos compétences. 👇',
                        ],
                    ],
                    'characterObjectives' => [
                        'title' => "Vos objectifs en tant que :{$example['characterKey']}: {$example['characterName']}",
                        'elements' => $example['characterObjectivesElements'],
                        'tutorial' => [
                            'title' => '**Plus d\'informations dans le tutoriel communautaire**',
                            'link' => $example['characterObjectivesTutorialLink'],
                        ],
                    ],
                    'externalResources' => [
                        'title' => 'Besoin d\'aide ? N\'hésitez pas à consulter',
                        'elements' => [
                            ['text' => 'Vos coéquipiers à l\'aide du :wall: **canal général**'],
                            ['text' => '**Le serveur Discord** d\'Eternaltwin', 'link' => 'https://discord.gg/Jb8Nwjck6r'],
                            ['text' => '**Les tutoriels communautaires** de l\'Aide aux bolets', 'link' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/'],
                            ['text' => '**Le wiki** Twinpédia', 'link' => 'https://twin.tithom.fr/mush'],
                        ],
                    ],
                ],
            ],
            actual: $this->normalizer->normalize($channel, format: null, context: ['currentPlayer' => $player])
        );
    }

    public function shouldNormalizeMushTipsIfPlayerIsMush(FunctionalTester $I): void
    {
        $player = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $I->grabService(StatusService::class)->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $player,
            tags: [],
            time: new \DateTime()
        );

        $channel = Channel::createTipsChannel();
        $channel->setDaedalus($this->daedalus->getDaedalusInfo());

        $normalizedChannel = $this->normalizer->normalize($channel, format: null, context: ['currentPlayer' => $player]);

        $I->assertEquals(
            expected: [
                'title' => 'Vos objectifs en tant que :berzerk: Mush',
                'elements' => [
                    '**Éliminer tous les humains** en coopérant avec les autres Mushs.',
                    '**Contaminer des humains** pour les transformer en Mushs.',
                    '**Manipuler les humains** pour les pousser à faire des actions qui les désavantagent.',
                    '**Saboter les actions humaines** aux bons moments, pour les empêcher de réparer le PILGRED et rentrer sur Sol.',
                ],
                'tutorial' => [
                    'title' => '**Plus d\'informations dans le tutoriel communautaire**',
                    'link' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/etre-mush-premiers-pas.html',
                ],
            ],
            actual: $normalizedChannel['tips']['teamObjectives']
        );

        $I->assertEquals(
            expected: $normalizedChannel['tips']['externalResources']['elements'][0]['text'],
            actual: 'Vos coéquipiers à l\'aide du :mush: **canal Mush**'
        );
    }

    private function shouldNormalizeChannelNameDescriptionAndTipsForPlayerDataProvider(): array
    {
        return [
            [
                'characterKey' => 'andie',
                'characterName' => 'Andie',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Récolter les débris** des hunters éliminés avec le Pasiphae en Baie Alpha 2 pour réparer l\'armure du vaisseau.',
                    '**Lancer des expéditions** sur des planètes avec Icarus en Baie Icarus pour récolter des ressources.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/andie-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'chao',
                'characterName' => 'Chao',
                'characterObjectivesElements' => [
                    '**Enquêter pour identifier les Mushs** avec vos coéquipiers humains et vos canaux privés.',
                    '**Torturer les coéquipiers suspects** afin d\'identifier des actions Mushs grâce à votre compétence Bourreau.',
                    '**Participer à des expéditions** pour récolter plus de ressources grâce à votre compétence Survie.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/chao-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'chun',
                'characterName' => 'Chun',
                'characterObjectivesElements' => [
                    '**Identifier des coéquipiers humains**.',
                    '**Enquêter pour identifier les Mushs** avec vos coéquipiers humains et vos canaux privés.',
                    '**Soigner les coéquipiers blessés** avec le Médikit ou à l\'Infirmerie.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/chun-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'derek',
                'characterName' => 'Derek',
                'characterObjectivesElements' => [
                    '**Identifier des coéquipiers humains**.',
                    '**Enquêter pour identifier les Mushs** avec vos coéquipiers humains et vos canaux privés.',
                    '**Participer à des expéditions** pour les rendre plus sûres grâce à votre compétence Tireur.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/derek-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'eleesha',
                'characterName' => 'Eleesha',
                'characterObjectivesElements' => [
                    '**Identifier des coéquipiers humains**.',
                    '**Enquêter pour identifier les Mushs** avec vos coéquipiers humains et vos canaux privés.',
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/eleesha-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'finola',
                'characterName' => 'Finola',
                'characterObjectivesElements' => [
                    '**Participer aux recherches** à l\'aide du Laboratoire de Recherche au Laboratoire pour affaiblir les Mushs.',
                    '**Soigner les coéquipiers blessés** avec le Médikit ou à l\'Infirmerie.',
                    '**Participer à des expéditions** pour les rendre plus sûres à l\'aide de votre compétence Diplomatie.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/finola-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'frieda',
                'characterName' => 'Frieda',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Récolter les débris** des hunters éliminés avec le Pasiphae en Baie Alpha 2 pour réparer l\'armure du vaisseau.',
                    '**Lancer des expéditions** sur des planètes avec Icarus en Baie Icarus pour récolter des ressources.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/frieda-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'gioele',
                'characterName' => 'Gioele',
                'characterObjectivesElements' => [
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Scanner des planètes** à l\'aide du Terminal Astro sur le Pont pour organiser des expéditions.',
                    '**Réparer l\'équipement vital** du vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/gioele-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'hua',
                'characterName' => 'Hua',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Récolter les débris** des hunters éliminés avec le Pasiphae en Baie Alpha 2 pour réparer l\'armure du vaisseau.',
                    '**Lancer des expéditions** sur des planètes avec Icarus en Baie Icarus pour récolter des ressources.',
                    '**Réparer l\'équipement vital** du vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/hua-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'ian',
                'characterName' => 'Ian',
                'characterObjectivesElements' => [
                    '**Arroser les plantes** assoiffées et désséchées.',
                    '**Traiter les plantes** malades.',
                    '**Semer les fruits** dans les hydropots.',
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/ian-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'janice',
                'characterName' => 'Janice',
                'characterObjectivesElements' => [
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Réconforter les coéquipiers démoralisés** afin d\'améliorer leur moral.',
                    '**Être présente auprès de coéquipiers allongés** afin d\'améliorer leur moral.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/janice-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'jin_su',
                'characterName' => 'Jin Su',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Récolter les débris** des hunters éliminés avec le Pasiphae en Baie Alpha 2 pour réparer l\'armure du vaisseau.',
                    '**Orienter et faire voyager le vaisseau** à l\'aide du Terminal de Commandement sur le Pont pour explorer des planètes.',
                    '**Donner des Discours enflammés** aux coéquipiers démoralisés pour améliorer leur moral.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/jin-su-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'kuan_ti',
                'characterName' => 'Kuan Ti',
                'characterObjectivesElements' => [
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Participer à la réparation du PILGRED** en Salle des Moteurs pour rentrer sur Sol.',
                    '**Réparer l\'équipement vital** du vaisseau.',
                    '**Donner des Discours enflammés** aux coéquipiers démoralisés pour améliorer leur moral.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/kuan-ti-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'paola',
                'characterName' => 'Paola',
                'characterObjectivesElements' => [
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Scanner des planètes** à l\'aide du Terminal Astro sur le Pont pour organiser des expéditions.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/paola-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'raluca',
                'characterName' => 'Raluca',
                'characterObjectivesElements' => [
                    '**Participer à la réparation du PILGRED** en Salle des Moteurs pour rentrer sur Sol.',
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Réparer l\'équipement vital** du vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/raluca-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':female_admin: humaine',
            ],
            [
                'characterKey' => 'roland',
                'characterName' => 'Roland',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Récolter les débris** des hunters éliminés avec le Pasiphae en Baie Alpha 2 pour réparer l\'armure du vaisseau.',
                    '**Lancer des expéditions** sur des planètes avec Icarus en Baie Icarus pour récolter des ressources.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/roland-tutoriel.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'stephen',
                'characterName' => 'Stephen',
                'characterObjectivesElements' => [
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Scanner des planètes** à l\'aide du Terminal Astro sur le Pont pour organiser des expéditions.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/stephen-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
            [
                'characterKey' => 'terrence',
                'characterName' => 'Terrence',
                'characterObjectivesElements' => [
                    '**Éliminer les hunters** avec les Patrouilleurs dans les Baies pour protéger le vaisseau.',
                    '**Participer aux projets NERON** à l\'aide du cœur de NERON au Nexus pour améliorer le vaisseau.',
                    '**Réparer l\'équipement vital** du vaisseau.',
                ],
                'characterObjectivesTutorialLink' => 'https://cmnemoi.github.io/archive_aide_aux_bolets/terrence-premiers-pas.html',
                'teamObjectivesTitle' => 'Vos objectifs en tant qu\':male_admin: humain',
            ],
        ];
    }
}
