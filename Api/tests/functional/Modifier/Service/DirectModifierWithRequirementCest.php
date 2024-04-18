<?php

namespace Mush\Tests\functional\Modifier\Service;

use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DirectModifierWithRequirementCest extends AbstractFunctionalTest
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->modifierCreationService = $I->grabService(ModifierCreationServiceInterface::class);
    }

    public function testAppliesDirectModifierWithModifierRequirement(FunctionalTester $I)
    {
        $initMoralPoint = $this->player1->getMoralPoint();

        // Given a direct modifier that reduces moral point only if there is more than 4 player in the room
        $modifierRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'player_in_room_four_people']);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setQuantity(-3)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('test_direct_modifier');
        $I->haveInRepository($eventConfig);

        $modifier = new DirectModifierConfig('test_direct_modifier_with_requirement');
        $modifier
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setTriggeredEvent($eventConfig)
            ->setRevertOnRemove(false)
            ->addModifierRequirement($modifierRequirement);

        $I->haveInRepository($modifier);

        // When applying the modifier
        $this->modifierCreationService->createModifier($modifier, $this->player1, [], new \DateTime());

        // then the amount of moral point should not have changed (requirement not met)
        $I->assertEquals($initMoralPoint, $this->player1->getMoralPoint());

        // Given there is more players in room
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FINOLA);

        // When applying the modifier
        $this->modifierCreationService->createModifier($modifier, $this->player1, [], new \DateTime());

        // then the amount of moral point should have diminished (requirement is met)
        $I->assertEquals($initMoralPoint - 3, $this->player1->getMoralPoint());
    }
}
