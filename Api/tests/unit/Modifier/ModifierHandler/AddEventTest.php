<?php

declare(strict_types=1);

namespace Mush\tests\unit\Modifier\ModifierHandler;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierHandler\AddEvent;
use Mush\Modifier\Service\EventCreationService;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddEventTest extends TestCase
{
    private AddEvent $addEvent;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $eventCreationService = new EventCreationService(
            new InMemoryGameEquipmentRepository()
        );

        $this->addEvent = new AddEvent($eventCreationService);
    }

    public function testShouldHandleEventModifierForThalasso(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a thalasso equipment
        $thalasso = GameEquipmentFactory::createEquipmentByNameForHolder(
            name: EquipmentEnum::THALASSO,
            holder: $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)
        );

        // given two players in Thalasso's room
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $jinSu = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::JIN_SU, $daedalus);

        $actionEvent = new ActionEvent(
            ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOWER)),
            actionProvider: $thalasso,
            player: $chun,
        );
        $actionEvent->addTag($chun->getLogName());

        // given this thalasso equipment has a modifier
        $modifier = new GameModifier(
            holder: $thalasso,
            modifierConfig: $this->getThalassoModifierConfig()
        );

        // when I handle add event modifier for thalasso
        $eventChain = $this->addEvent->handleEventModifier(
            modifier: $modifier,
            events: new EventChain([$actionEvent]),
            eventName: ActionEvent::POST_ACTION,
            tags: [ActionEnum::SHOWER->value, ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER],
            time: new \DateTime()
        );

        // then event chain should contain a single player variable event for the player who triggered the event (Chun)
        self::assertCount(1, $eventChain->filter(static fn ($event) => $event instanceof PlayerVariableEvent));
    }

    private function getThalassoModifierConfig(): TriggerEventModifierConfig
    {
        $eventConfigPlus2MovementPoint = new VariableEventConfig();
        $eventConfigPlus2MovementPoint
            ->setQuantity(2)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_+2movementPoint');

        $thalassoMovementPointModifierConfig = new TriggerEventModifierConfig('modifier_for_player_set_+2movementPoint_on_post.action_if_reason_shower');
        $thalassoMovementPointModifierConfig
            ->setTriggeredEvent($eventConfigPlus2MovementPoint)
            ->setTargetEvent(ActionEvent::POST_ACTION)
            ->setPriority(ModifierPriorityEnum::AFTER_INITIAL_EVENT)
            ->setTagConstraints([
                ActionEnum::SHOWER->value => ModifierRequirementEnum::ANY_TAGS,
                ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
                ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER => ModifierRequirementEnum::NONE_TAGS,
            ])
            ->setApplyWhenTargeted(true)
            ->setModifierName(ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER)
            ->setModifierRange(ModifierHolderClassEnum::EQUIPMENT);

        return $thalassoMovementPointModifierConfig;
    }
}
