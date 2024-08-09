# Skill

This module handles player skills.

Skills grant players new actions, various bonuses through [modifiers](../Modifier/README.md), free action points through [skill points](../Status/Enum/SkillPointsEnum.php) or new equipment.

# Directory Tree:
    |-- ConfigData
    |-- DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- UseCase

## How to add a new skill?
- Add the skill to the [SkillConfigData](./ConfigData/SkillConfigData.php).
- Configure skill modifiers in [ModifierConfigData](../Modifier/ConfigData/ModifierConfigData.php) and [SkillModifierConfigFixtures](../Modifier/DataFixtures/SkillModifierConfigFixtures.php). Please read the Modifier module [documentation](../Modifier/README.md) to see what can be implemented with modifiers, and how.
- Configure skill points :
    - Add a new [ActionTypeEnum](../Action/Enum/ActionTypeEnum.php) for the actions which should be free when the player has the added skill points.
    - Add this new action type to relevant actions in [ActionData](../Action/ConfigData/ActionData.php) and [ActionsFixtures](../Action/DataFixtures/ActionsFixtures.php).
    - Add a ChargeStatus in [StatusConfigData](../Status/ConfigData/StatusConfigData.php) and [ChargeStatusFixtures](../Status/DataFixtures/ChargeStatusFixtures.php). This status will hold the skill points, increment them at the end of the day, and decrement them when player do actions. Don't forget to add the status to the [GameConfigData](../Game/ConfigData/GameConfigData.php).
    - Add a modifier in [ModifierConfigData](../Modifier/ConfigData/ModifierConfigData.php) and [ChargeStatusFixtures](../Status/DataFixtures/ChargeStatusFixtures.php) which will make the needed actions free if the player has skill points.
    - Add skill points gain log in [StatusEventLogEnum](../RoomLog/Enum/StatusEventLogEnum.php).
    - Add skill points french translations in [player+intl-icu.fr.xlf](../../translations/fr/player+intl-icu.fr.xlf).
- Configure the equipment spawned by the skill in [SpawnEquipmentConfigData](../Equipment/ConfigData/SpawnEquipmentConfigData.php).
- Add the skill to a character in [CharacterConfigData](../Player/ConfigData/CharacterConfigData.php) and [CharacterConfigFixtures](../Player/DataFixtures/CharacterConfigFixtures.php).
- Add french translations in [skill+intl-icu.fr.xlf](../../translations/fr/skill+intl-icu.fr.xlf).

If you want to implement a custom behavior which cannot be covered by the options above, you will need to check for Skill presence and implement your logic directly in the services / normalizers... Do not hesitate to contact us if you need help.