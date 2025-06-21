<?php

namespace Mush\tests\functional\Daedalus\Normalizer;

use Mush\Daedalus\Entity\DaedalusProjectsStatistics;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Daedalus\Normalizer\ClosedDaedalusNormalizer;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ClosedDaedalusNormalizerCest extends AbstractFunctionalTest
{
    private ClosedDaedalusNormalizer $normalizer;
    private DaedalusService $daedalusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(ClosedDaedalusNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function shouldNormalizeDaedalusStatisticsCorrectly(FunctionalTester $I): void
    {
        $finishedDaedalus = $this->createDaedalus($I);
        $daedalusStatistics = new DaedalusStatistics(planetsFound: 1, explorationsStarted: 2, shipsDestroyed: 5, rebelBasesContacted: 1, sporesCreated: 4, mushAmount: 2);

        $finishedDaedalus->getDaedalusInfo()->setDaedalusStatistics($daedalusStatistics);

        $closedDaedalus = $this->daedalusService->endDaedalus($finishedDaedalus, 'super_nova', new \DateTime());

        // when i normalize
        $normalizedDaedalus = $this->normalizer->normalize($closedDaedalus);

        $I->assertEquals(
            expected: [
                [
                    'name' => 'planetsFound',
                    'value' => 1,
                ],
                [
                    'name' => 'explorationsStarted',
                    'value' => 2,
                ],
                [
                    'name' => 'shipsDestroyed',
                    'value' => 5,
                ],
                [
                    'name' => 'sporesCreated',
                    'value' => 4,
                ],
                [
                    'name' => 'mushAmount',
                    'value' => 2,
                ],
                [
                    'name' => 'rebelBasesContacted',
                    'value' => 1,
                ],
            ],
            actual: $normalizedDaedalus['statistics']['lines']
        );
    }

    public function shouldNormalizeDaedalusProjectsCorrectly(FunctionalTester $I): void
    {
        // given it has a new DaedalusProjectsStatistics
        $this->daedalus->getDaedalusInfo()->setDaedalusProjectsStatistics(new DaedalusProjectsStatistics());

        // given a project of each category is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ANABOLICS),
            $this->chun,
            $I
        );
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            $this->chun,
            $I
        );
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PILGRED),
            $this->chun,
            $I
        );

        // given the daedalus is destroyed
        $closedDaedalus = $this->daedalusService->endDaedalus($this->daedalus, 'super_nova', new \DateTime());

        // when i normalize
        $normalizedDaedalus = $this->normalizer->normalize($closedDaedalus);

        $I->assertEquals(
            expected: [
                [
                    'type' => 'neron_project',
                    'key' => 'armour_corridor',
                    'name' => 'Coursives blindées',
                    'description' => 'Les dégâts de chaque attaque subie par la coque du Daedalus sont diminués d\'un point.',
                    'lore' => 'Eurêka ! Dans Magellan, les fibres optiques qui longent les coursives peuvent se compresser automatiquement pour pouvoir rajouter facilement des câbles. En injectant des câbles en trop, vous pouvez créer une pseudo-armure !',
                ],
            ],
            actual: $normalizedDaedalus['projects']['neronProjects']['lines']
        );
        $I->assertEquals(
            expected: [
                [
                    'type' => 'research',
                    'key' => 'anabolics',
                    'name' => 'Anabolisant',
                    'description' => 'Génère 4 Anabolisants, qui peuvent être consommés pour donner 8 :pm: .',
                    'lore' => 'La molécule de Grempf stimule la création d\'hormones androgènes telles que la testostérone. Cela fonctionne sur les êtres humains quel que soit leur sexe, les animaux et même les plantes. Vous avez déjà entendu parler de la mystérieuse transhumance des séquoias du parc de Yosemite ? Bah voilà.',
                ],
            ],
            actual: $normalizedDaedalus['projects']['researchProjects']['lines']
        );
        $I->assertEquals(
            expected: [
                [
                    'type' => 'pilgred',
                    'key' => 'pilgred',
                    'name' => 'PILGRED',
                    'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre. De plus, la machine à café se régénèrera quatre fois plus vite.',
                    'lore' => '',
                ],
            ],
            actual: $normalizedDaedalus['projects']['pilgredProjects']['lines']
        );
    }

    public function shouldNormalizeTitleDaedalusTitleHoldersCorrectly(FunctionalTester $I): void
    {
        // given titles are assigned (Kuan Ti has priority over Chun for all of them)
        $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());

        // given title holder registers Chun as comms manager
        $this->daedalus->getDaedalusInfo()->addTitleHolder(TitleEnum::COM_MANAGER, $this->chun->getLogName());

        // given the daedalus is destroyed
        $closedDaedalus = $this->daedalusService->endDaedalus($this->daedalus, 'super_nova', new \DateTime());

        // when i normalize
        $normalizedDaedalus = $this->normalizer->normalize($closedDaedalus);

        $I->assertEqualsCanonicalizing(
            expected: [
                [
                    'title' => 'commander',
                    'characterKeys' => ['kuan_ti'],
                ],
                [
                    'title' => 'neron_manager',
                    'characterKeys' => ['kuan_ti'],
                ],
                [
                    'title' => 'com_manager',
                    'characterKeys' => ['kuan_ti', 'chun'],
                ],
            ],
            actual: $normalizedDaedalus['titleHolders']
        );
    }

    public function shouldNormalizeDaedalusFunFactsCorrectly(FunctionalTester $I): void
    {
        // given Kuan Ti removed from the game
        $this->daedalus->removePlayer($this->kuanTi);

        // given the daedalus is returns to Sol
        $closedDaedalus = $this->daedalusService->endDaedalus($this->daedalus, EndCauseEnum::SOL_RETURN, new \DateTime());

        // when i normalize
        $normalizedDaedalus = $this->normalizer->normalize($closedDaedalus);

        $I->assertEqualsCanonicalizing(
            expected: [
                [
                    'title' => 'Pureté stupéfiante',
                    'description' => 'La drogue c\'est mal, n\'y touchez pas les enfants. N\'y touchez pas vous non plus, ou je vous fume.',
                    'characterKey' => 'chun',
                ],
                [
                    'title' => 'En mode silence-radio',
                    'description' => 'Celui-là n\'a visiblement jamais trouvé le bouton du talkie-walkie...',
                    'characterKey' => 'chun',
                ],
                [
                    'title' => 'Glandeur invertébré',
                    'description' => 'Ne s\'est pas trop foulé, même si c\'est la fin du monde. Non, pour lui, ce sont les autres qui triment.',
                    'characterKey' => 'chun',
                ],
                [
                    'title' => 'SuperOptimisator',
                    'description' => 'Il n\'a presque jamais gaspillé de point d\'action. Parce que lui, il a compris le but du jeu.',
                    'characterKey' => 'chun',
                ],
                [
                    'title' => 'La violence c\'est le mal',
                    'description' => 'Pacifiste inconditionnel qui ne fait presque de mal à personne. Ce sont les autres qui s\'en occupent.',
                    'characterKey' => 'chun',
                ],
            ],
            actual: $normalizedDaedalus['funFacts']
        );
    }
}
