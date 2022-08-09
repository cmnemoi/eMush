# Modifier
This module handle modifiers.
Modifiers change variables of action and events, for example reducing the cost of a specific action.
This module handle both the computation of modified cost and output,
but also create modifiers listening to other modules.
For example creating modifiers on a player when it takes gears in its inventory.

# Architecture 

## Directory Tree:
    |-- config
    |-- DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Service

## Repositories

### Entity
- Modifier:
  - apply either to a player, equipment, room and daedalus
  - optional: a charge status, modifier is only active if charge is above 0
  - modifierConfig
- ModifierConfig
  - gameConfig
  - name: key of the modifier
  - delta: the amount of modification to the quantity.
  - target: What parameter is modified by the modifier ([player variable](../Player/Enum/PlayerVariableEnum.php), [Daedalus variable](../Daedalus/Enum/DaedalusVariableEnum.php) or[target](./Enum/ModifierTargetEnum.php))
  - scope: on which event or action is the modifier applied (either the name of the action event or one of the additional [scope](./Enum/ModifierScopeEnum.php))
  - [reach](./Enum/ModifierReachEnum.php): Entity class to which the modifier will be linked
  - [mode](./Enum/ModifierModeEnum.php): how the input value is modified
  - modifierCondition: a collection of conditions that must be complied for the modifier to apply

### Listener
- Listen to other modules that may create or delete modifiers.
  - Action: take, drop and move
  - Diseases: getting and curring from a diseases
  - EquipmentInit: equipment creation
  - Equipment: equipment destroyed, equipment transformed, equipment falling from the inventory when created
  - Player: player death
  - Status: equipment broken or repaired, modifiers associated with the added or removed status
- Listen to events to add changes added by modifiers
  - Cycle: add new effects (ex: life decrease caused by hunger)
  - Quantity: increase or decrease the quantity according to modifiers

### Data Fixtures
How to create a new modifier: 
- Create a new modifierConfig 
- Add the modifier config in the status config or gear config

