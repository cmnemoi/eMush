# Project

This module handles PILGRED, NERON projects and researches gameplay.

A project is an improvement which affect the whole Daedalus. They are divided in three categories :

- NERON projects add new equipment, improve events cost and output through modifiers or have special effects directly in the services.
- PILGRED improves the coffee machine and unlocks the two good endings of the game.
- Researches are focused on Mush vs Human gameplay : maluses for mush players though modifiers, or new equipment helping Mush hunt.

Players need to particpate to a project to unlock their benefits. However, when they participate in a project, their efficiency drops and can be improved only when another player participates in the same project.

For an exhaustive list of features introduced by this module, please refer to the [0.8](https://gitlab.com/eternaltwin/mush/mush/-/milestones/9) and [0.10](https://gitlab.com/eternaltwin/mush/mush/-/milestones/20) Milestone pages.

# Incomplete data model (as always) ![hello](https://gitlab.com/eternaltwin/mush/mush/uploads/c91254cda079e8e05f869a11c48612e9/image.png?height=300&width=300)

# Directory Tree:
    |-- config
    |-- ConfigData
    |-- DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- UseCase

## How to add a new project?
- Add the project to the [ProjectConfigData](./ConfigData/ProjectConfigData.php).
- Configure project modifiers in [ModifierConfigData](../Modifier/ConfigData/ModifierConfigData.php) and [ProjectModifierConfigFixtures](../Modifier/DataFixtures/ProjectModifierConfigFixtures.php). Please read the Modifier module [documentation](../Modifier/README.md) to see what can be implemented with modifiers, and how.
- Configure equipment spawned by the project in [SpawnEquipmentConfigData](../Equipment/ConfigData/SpawnEquipmentConfigData.php)
- Configure equipment replaced by the project in [ReplaceEquipmentConfigData](../Equipment/ConfigData/ReplaceEquipmentConfigData.php)

If you want custom behavior which is not covered by the options above, you will need to check for project activation and implement the logic directly in the services.
- Add the project to [GameConfigData](../Game/ConfigData/GameConfigData.php).
- Add french translations in [project+intl-icu.fr.xlf](../../translations/fr/project+intl-icu.fr.xlf).


