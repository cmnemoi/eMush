# Modifier
This module handle modifiers.
Modifiers change events, for example reducing the cost of a specific action or triggering an extra event.
This module handle both the computation of modified cost and output,
but also create modifiers listening to other modules.
For example creating modifiers on a player when it takes gears in its inventory.

### Why using modifiers?

The strength of modifiers is that many equipments can modify events, even from remote places.
Rather than checking every gear in every room every time the player check the cost of an action, modification are stored on 4 critical entities (Player, Daedalus, GameEquipment and Place) and are easily accessible.

### What can I implement with modifiers?

- Increasing or decreasing the amount of points of a VariableEvent (action, movement, hull...)
- Changing the cost of an action
- Preventing an event from being called
- Triggering an additional event before a given event

# Architecture 

## Directory Tree:
    |-- config
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- |-- Config
    |-- |-- Collection
    |-- Enum
    |-- Event
    |-- Listener
    |-- Service

## Repositories

### Entity
- [GameModifier](./Entity/GameModifier.php):
  - holder: a ModifierHolderInterface (player, equipment, room or daedalus)
  - optional: a charge status, modifier is only active if charge is above 0
  - modifierConfig
  
- [AbstractModifierConfig](./Entity/Config/AbstractModifierConfig.php): 3 child classes
  - [DirectModifierConfig](./Entity/Config/DirectModifierConfig.php):
    - This config do not create a gameModifier, instead, an event is triggered.
    - The event can be reverted when the source of the modifier is no longer available
    - Typical use cases: change max healthPoints of a player - create equipment on skill selection
  - [TriggerEventModifierConfig](./Entity/Config/TriggerEventModifierConfig.php):
    - Create a GameModifier and trigger an event whenever a targetEvent is dispatched
    - the triggered event can replace the initial event
    - the triggered event can be set to null
    - Typical use cases: modify a player variable on cycle change - prevent an event from being triggered
  - [VariableEventModifierConfig](./Entity/Config/VariableEventModifierConfig.php):
    - Create a GameModifier and modify the quantity variable of an VariableEventInterface
    - Typical use cases: change the cost of an action - change the success rate of an action - increase or decrease the modification of a Daedalus or Player variable


### Listener
- Listen to other modules that may create or delete modifiers.
  - Action: take, drop and move
  - Diseases: getting and curring from a diseases
  - EquipmentInit: equipment creation
  - Equipment: equipment destroyed, equipment transformed, equipment falling from the inventory when created
  - Player: player death
  - Status: equipment broken or repaired, modifiers associated with the added or removed status.


### Data Fixtures
How to create a new modifier: 
- Create a new modifierConfig 
- Add the modifier config in the status, gear or disease config

