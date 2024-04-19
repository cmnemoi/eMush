<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectConfigFactory;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class TerminalNormalizerCest extends AbstractFunctionalTest
{
    private TerminalNormalizer $terminalNormalizer;

    private CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->terminalNormalizer = $I->grabService(TerminalNormalizer::class);
        $this->terminalNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->createProjectFromConfigForDaedalusUseCase = $I->grabService(CreateProjectFromConfigForDaedalusUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given PILGRED is created on this Daedalus
        $config = ProjectConfigFactory::createPilgredConfig();
        $I->haveInRepository($config);

        $this->createProjectFromConfigForDaedalusUseCase->execute(
            projectConfig: $config,
            daedalus: $this->daedalus,
        );
    }

    public function testShouldNormalizePilgredTerminal(FunctionalTester $I): void
    {
        // given I have a PILGRED terminal
        $terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $terminal
        );

        // when I normalize the terminal for Chun
        $normalizedTerminal = $this->terminalNormalizer->normalize($terminal, format: null, context: ['currentPlayer' => $this->chun]);

        // then I should get the normalized terminal
        $I->assertEquals(expected: ProjectName::PILGRED->value, actual: $normalizedTerminal['key']);
        $I->assertEquals(expected: 'Réacteur PILGRED', actual: $normalizedTerminal['name']);
        $I->assertEquals(
            expected: 'La remise en état de PILGRED demande des compétences énormes. Il vous faudra vous armer de patience et survivre pour y aboutir. Une fois réparé, la possibilité de revenir sur Sol vous sera ouverte. Qu\'en ferez-vous ?',
            actual: $normalizedTerminal['tips']
        );
        $I->assertEquals(
            expected: ['exit_terminal'],
            actual: array_map(static fn ($action) => $action['key'], $normalizedTerminal['actions'])
        );
        $I->assertEquals(
            expected: [[
                'key' => 'pilgred',
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 1-1%',
                'bonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des avantages pour réparer PILGRED.//• Accorde 1 :pa_pilgred: (point d\'action de **réparation de PILGRED**) par jour.//• Bonus pour développer certains **Projets NERON**.',
                    ],
                    [
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//• +1 :pa_eng: (point d\'action **Réparation**) par jour.//• Chances de réussites doublées pour les **Réparations**.//• Chances de réussites doublées pour les **Rénovations**.//• Bonus pour développer certains **Projets NERON**.',
                    ],
                ],
            ]],
            actual: $normalizedTerminal['projects']
        );
    }
}
