<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class CoffeeCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private TranslationServiceInterface $translationService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->translationService = $I->grabService(TranslationServiceInterface::class);
    }

    public function shouldDisplayGuaranaBonusToChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef($I);
        $coffee = $this->givenPlayerHasFood(GameRationEnum::COFFEE);
        $this->givenGuaranaResearchIsFinished($I);

        $normalizedCoffee = $this->equipmentNormalizer->normalize(
            $coffee,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur les effets :',
                'effects' => [
                    '+ 3 :pa:',
                ],
            ],
            actual: $normalizedCoffee['effects']
        );
    }

    private function givenPlayerIsAChef(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CHEF, $I);
    }

    private function givenPlayerHasFood(string $ration): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $ration,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenGuaranaResearchIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::GUARANA_CAPPUCCINO),
            author: $this->player,
            I: $I
        );
    }
}
