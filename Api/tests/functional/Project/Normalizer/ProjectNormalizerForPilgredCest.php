<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Normalizer\ProjectNormalizer;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ProjectNormalizerForPilgredCest extends AbstractFunctionalTest
{
    private ProjectNormalizer $projectNormalizer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $terminal;
    private int $repairActionId;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->projectNormalizer = $I->grabService(ProjectNormalizer::class);
        $this->projectNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given pilgred terminal in the room
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PILGRED,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given Chun is focused on the terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->terminal
        );

        $this->repairActionId = $this->terminal
            ->getMechanicActionByNameOrThrow(ActionEnum::REPAIR_PILGRED)
            ->getId();
    }

    public function shouldNormalizeProject(FunctionalTester $I): void
    {
        // given I have a project
        $project = $this->daedalus->getPilgred();

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project, null, ['currentPlayer' => $this->chun]);

        // then I should get the normalized project
        $I->assertEqualsIgnoringCase(
            expected: [
                'id' => $project->getId(),
                'key' => 'pilgred',
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre. De plus, la machine à café se régénèrera quatre fois plus vite.',
                'lore' => '',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 1-1%',
                'efficiencyTooltipHeader' => 'Efficacité',
                'efficiencyTooltipText' => 'Pour garder une efficacité optimale, alternez le travail avec un collègue.',
                'bonusSkills' => [
                    [
                        'key' => 'physicist',
                        'name' => 'Physicien',
                        'description' => "Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques quantiques et de l'essence même des cordes qui composent notre Univers est son atout. Il possède des avantages pour réparer PILGRED.//:point: Accorde 1 :pa_pilgred: (point d'action de **réparation de PILGRED**) par jour.//:point: Bonus pour développer certains **Projets NERON** et **réparer PILGRED**.",
                    ],
                    [
                        'key' => 'technician',
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.//
        :point: +1 :pa_eng: (point d\'action **Réparation**) par jour.//
        :point: Peut **Démonter** des objets.//
        :point: Peut **Renforcer** des objets.//
        :point: Chances de réussites doublées pour les **Réparations**.//
        :point: Chances de réussites doublées pour les **Rénovations**.//
        :point: Bonus pour développer certains **Projets NERON** et **réparer PILGRED**.',
                    ],
                ],
                'isLastAdvancedProject' => false,
                'actions' => [
                    [
                        'id' => $this->repairActionId,
                        'key' => ActionEnum::REPAIR_PILGRED->value,
                        'name' => 'Participer',
                        'actionPointCost' => 2,
                        'movementPointCost' => 0,
                        'moralPointCost' => 0,
                        'skillPointCosts' => [],
                        'successRate' => 100,
                        'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
                        'canExecute' => true,
                        'confirmation' => null,
                        'actionProvider' => ['class' => $this->terminal::class, 'id' => $this->terminal->getId()],
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }

    public function shouldNormalizeProjectInDaedalusNormalizationContext(FunctionalTester $I): void
    {
        // given I have a project
        $project = $this->daedalus->getPilgred();

        // when I normalize the project in daedalus normalization context
        $normalizedProject = $this->projectNormalizer->normalize($project, null, [
            'currentPlayer' => $this->chun,
            'normalizing_daedalus' => true,
        ]);

        // then I should get the normalized project
        $I->assertEquals(
            expected: [
                'type' => 'pilgred',
                'translatedType' => 'PILGRED',
                'key' => 'pilgred',
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre. De plus, la machine à café se régénèrera quatre fois plus vite.',
                'lore' => '',
            ],
            actual: $normalizedProject
        );
    }
}
