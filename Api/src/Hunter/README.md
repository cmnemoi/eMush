# Hunter
This module handles hunters.

Hunters are hostile NPCs that attack the Daedalus. They can appear and deal damage to Daedalus' hull each cycle.

For an exhaustive list of features introduced by this module, please refer to the [0.6 Milestone page](https://gitlab.com/eternaltwin/mush/mush/-/milestones/16#tab-issues).

# Data model: ![hunter data model](https://gitlab.com/eternaltwin/mush/mush/uploads/c33063b2c328de39ab90ab42cc79c9ca/hunter_class_diagram.drawio.svg?height=300&width=300)
(incomplete and outdated, but the general idea is here)

# Directory Tree:
    |-- config
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service

# Events

- `HunterEvent::HUNTER_NEW_CYCLE` is dispatched at the beginning of each cycle. It is used to dispatch all the other events which should happen at the beginning of each cycle.
- `HunterPoolEvent::UNPOOL_HUNTERS` is the event dispatched to summon new hunters.
- `HunterEvent::HUNTER_DEATH` is dispatched to kill a hunter.

# Misc

The Daedalus has a `HunterPoints` amount to spend at each cycle to summon hunters computed in the `Game` `DifficultyService`.

Each hunter summoned costs a certain amount of `HunterPoints`, different for each type of hunter (hunters, arack, asteroids, etc.).
