<?php

namespace Mush\Modifier\Factory;

use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Dto\TriggerEventModifierConfigDto;
use Mush\Modifier\Dto\VariableEventModifierConfigDto;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;

final class GameModifierFactory
{
    public static function createByNameForHolder(string $name, ModifierHolderInterface $holder): GameModifier
    {
        $modifierConfigDataDto = ModifierConfigData::getByName($name);

        $modifierConfig = match (true) {
            $modifierConfigDataDto instanceof VariableEventModifierConfigDto => VariableEventModifierConfig::fromDtoChild($modifierConfigDataDto),
            $modifierConfigDataDto instanceof TriggerEventModifierConfigDto => TriggerEventModifierConfig::fromDtoChild($modifierConfigDataDto),
            default => throw new \LogicException('Unsupported modifier type ' . $modifierConfigDataDto::class),
        };
        self::setupModifierConfigId($modifierConfig);

        $modifier = new GameModifier($holder, $modifierConfig);
        self::setupModifierId($modifier);

        return $modifier;
    }

    private static function setupModifierConfigId(AbstractModifierConfig $modifierConfig): void
    {
        $hash = crc32(serialize($modifierConfig));

        (new \ReflectionProperty($modifierConfig, 'id'))->setValue($modifierConfig, $hash);
    }

    private static function setupModifierId(GameModifier $modifier): void
    {
        $hash = crc32(serialize($modifier));

        (new \ReflectionProperty($modifier, 'id'))->setValue($modifier, $hash);
    }
}
