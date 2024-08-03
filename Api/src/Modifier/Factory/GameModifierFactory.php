<?php

namespace Mush\Modifier\Factory;

use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;

final class GameModifierFactory
{
    public static function createByNameForHolder(string $name, ModifierHolderInterface $holder): GameModifier
    {
        $modifierConfigData = ModifierConfigData::getByName($name);

        $modifierConfig = match ($modifierConfigData['type']) {
            'variable_event_modifier' => VariableEventModifierConfig::fromConfigData($modifierConfigData),
            default => throw new \LogicException("Unsupported modifier type {$modifierConfigData['type']}"),
        };
        self::setupModifierConfigId($modifierConfig);

        $modifier = new GameModifier($holder, $modifierConfig);
        self::setupModifierId($modifier);

        return $modifier;
    }

    private static function setupModifierConfigId(VariableEventModifierConfig $modifierConfig): void
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
