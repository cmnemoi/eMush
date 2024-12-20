<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Normalizer;

use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PlayerNormalizerCest extends AbstractFunctionalTest
{
    private CurrentPlayerNormalizer $currentPlayerNormalizer;
    private NormalizerInterface $normalizer;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private ActionConfig $dropConfig;
    private Drop $dropAction;

    private ActionConfig $takeConfig;
    private Take $takeAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->currentPlayerNormalizer = $I->grabService(CurrentPlayerNormalizer::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->normalizer = $I->grabService(NormalizerInterface::class);

        $this->dropConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DROP]);
        $this->dropAction = $I->grabService(Drop::class);

        $this->takeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->takeAction = $I->grabService(Take::class);

        $this->currentPlayerNormalizer->setNormalizer($this->normalizer);
    }

    public function testPlayerItemsNormalization(FunctionalTester $I): void
    {
        $this->givenPlayerHasPostIt();
        $this->givenPlayerHasBacta();

        $normalizedPlayer = $this->whenPlayerIsNormalized();

        $this->thenItemsShouldBeInOriginalOrder($normalizedPlayer, $I);
    }

    public function testPlayerItemsAreNormalizedInAStackFashionAfterAManipulation(FunctionalTester $I): void
    {
        $postIt = $this->givenPlayerHasPostIt();
        $this->givenPlayerHasBacta();
        $this->givenPlayerDropsItem($postIt);
        $this->givenPlayerTakesItem($postIt);

        $normalizedPlayer = $this->whenPlayerIsNormalized();

        $this->thenItemsShouldBeInStackOrder($normalizedPlayer, $I);
    }

    public function shouldNotNormalizeSameActionGivenByMultipleSkills(FunctionalTester $I): void
    {
        $this->givenPlayerHasSkills([SkillEnum::SOLID, SkillEnum::WRESTLER], $I);

        $normalizedPlayer = $this->whenPlayerIsNormalized();

        $this->thenPlayerShouldHaveOnePutThroughDoorAction($normalizedPlayer, $I);
    }

    public function shouldNormalizeOneActionBySimilarEquipmentInPlayerInventory(FunctionalTester $I): void
    {
        $this->givenPlayerHasSkill(SkillEnum::SOLID, $I);
        $this->givenPlayerHasBlasters(2);

        $normalizedPlayer = $this->whenPlayerIsNormalized();

        $this->thenPlayerShouldHaveTwoShootActions($normalizedPlayer, $I);
    }

    private function givenPlayerHasPostIt(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasBacta(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameDrugEnum::BACTA,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerDropsItem(GameEquipment $item): void
    {
        $this->dropAction->loadParameters($this->dropConfig, $item, $this->player, $item);
        $this->dropAction->execute();
    }

    private function givenPlayerTakesItem(GameEquipment $item): void
    {
        $this->takeAction->loadParameters($this->takeConfig, $item, $this->player, $item);
        $this->takeAction->execute();
    }

    private function givenPlayerHasSkills(array $skills, FunctionalTester $I): void
    {
        foreach ($skills as $skill) {
            $this->addSkillToPlayer($skill, $I);
        }
    }

    private function givenPlayerHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->addSkillToPlayer($skill, $I);
    }

    private function givenPlayerHasBlasters(int $quantity): void
    {
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
            quantity: $quantity
        );
    }

    private function whenPlayerIsNormalized(): array
    {
        return $this->currentPlayerNormalizer->normalize($this->player, null, ['currentPlayer' => $this->player]);
    }

    private function thenItemsShouldBeInOriginalOrder(array $normalizedPlayer, FunctionalTester $I): void
    {
        $playerNormalizedItems = $normalizedPlayer['items'];
        $I->assertEquals(ItemEnum::POST_IT, $playerNormalizedItems[0]['key']);
        $I->assertEquals(GameDrugEnum::BACTA, $playerNormalizedItems[1]['key']);
    }

    private function thenItemsShouldBeInStackOrder(array $normalizedPlayer, FunctionalTester $I): void
    {
        $playerNormalizedItems = $normalizedPlayer['items'];
        $I->assertEquals(GameDrugEnum::BACTA, $playerNormalizedItems[0]['key']);
        $I->assertEquals(ItemEnum::POST_IT, $playerNormalizedItems[1]['key']);
    }

    private function thenPlayerShouldHaveOnePutThroughDoorAction(array $normalizedPlayer, FunctionalTester $I): void
    {
        $actions = $normalizedPlayer['room']['players'][0]['actions'];
        $I->assertCount(1, array_filter($actions, static fn (array $action) => $action['key'] === ActionEnum::PUT_THROUGH_DOOR->value));
    }

    private function thenPlayerShouldHaveTwoShootActions(array $normalizedPlayer, FunctionalTester $I): void
    {
        $actions = $normalizedPlayer['room']['players'][0]['actions'];
        $I->assertCount(2, array_filter($actions, static fn (array $action) => $action['key'] === ActionEnum::SHOOT->toString()));
    }
}
