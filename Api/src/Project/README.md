# Project

This module handles PILGRED, NERON projects and researches gameplay.

A project is an improvement which affect the whole Daedalus. They are divided in three categories :

- NERON projects add new equipment, improve events cost and output through modifiers or have special effects directly in the services / use cases.
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

## ConfigData and DataFixtures
[ProjectConfigData](./ConfigData/ProjectConfigData.php) contains the list of all projects. If you want to add a new project, you need to do it here.

DataFixtures for tests will be created automatically when you add a new project.

