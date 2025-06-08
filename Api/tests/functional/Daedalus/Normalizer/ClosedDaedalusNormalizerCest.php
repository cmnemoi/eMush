<?php

namespace Mush\tests\functional\Daedalus\Normalizer;

use Mush\Daedalus\Entity\DaedalusProjectsStatistics;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Daedalus\Normalizer\ClosedDaedalusNormalizer;
use Mush\Daedalus\Service\DaedalusService;
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
                    'name' => 'Planètes trouvées',
                    'value' => 1,
                ],
                [
                    'name' => 'Explorations',
                    'value' => 2,
                ],
                [
                    'name' => 'Vaisseaux détruits',
                    'value' => 5,
                ],
                [
                    'name' => 'Spores générés',
                    'value' => 4,
                ],
                [
                    'name' => 'Nombre de Mush',
                    'value' => 2,
                ],
                [
                    'name' => 'Bases rebelles contactées',
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
}
