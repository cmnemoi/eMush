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

### How to choose priority of a modifier ?
The choice of [priority](./Enum/PriorityEnum) depends on the type of eventModifier you are using:
- TriggerEventModifier: indicates if the triggered event is dispatched before (<0) or after (>0) the initial event. Several triggered events can be sorted one relative to another.
- PreventEvent: remove all the event with a priority higher than the modifier one. E.g. to prevent the initial event, chose -50.
- Variable modifiers: On which order is the initial event quantity modified? Overall, multiplicative modifiers should have a lower priority than additive ones. By default, use priority between -1 to -20.

### As a dev what should/can I modify in this module ?
- First of all, you can add as much config as you want with existing handler (eg you want to change an existing modifier or create a new modifier for disease)
- Maybe you need to implement new handlers to implement specific skills or project. In that case, you probably need to add this handler in Modifier/ModifierHandler (the EFFECT of the modifier need more logic than what is currently available)
- You want to implement a new feature that can create GameModifier (let say skills) :
  - You should have a new entity holding ModifierConfigs (eg Skill)
  - When your entity is created or deleted (eg Player pick a skill) dispatch an event
  - In Modifier/Listener/ add a new listener. Here you must call the function ModifierService->createModifierFromConfig. This function require a modifierHolder, hence in your listener you must have logic to find the GameModifierHolder from your event and the modifierConfig->getModifierRange().
- Adding a new ModifierHolder : In theory you should not do that unless you implement a new core entity

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
  - provider: the entity that provide the effect it can be a player, a project a gameEquipment
  - modifierConfig
  
- [AbstractModifierConfig](./Entity/Config/AbstractModifierConfig.php): 3 child classes
  - [DirectModifierConfig](./Entity/Config/DirectModifierConfig.php):
    - This config do not create a gameModifier, instead, an event is triggered.
    - The event can be reverted when the source of the modifier is no longer available
    - Typical use cases: change max healthPoints of a player - create equipment on skill selection
  - [EventModifierConfig](./Entity/Config/EventModifierConfig.php):
    - Create a GameModifier and trigger whenever a targetEvent is dispatched
    - The effect can be set thanks to the ModifierStrategy property
  


### Strategies of EventModifiers
- [PreventEvent](./ModifierHandler/PreventEvent.php):
  - Stop the dispatching of events
  - Typical use cases: prevent an event from being triggered
  - In game use example : apron prevent the event ApplyStatus if status is dirty
- [AddEvent](./ModifierHandler/AddEvent.php):
  - Specific for [TriggerEventModifierConfig](./Entity/Config/TriggerEventModifierConfig.php)
  - Create a new event that can be dispatched either after or before the initial event
  - Typical use cases: modify a player variable on cycle change
- [VariableModifier](./ModifierHandler/VariableModifier.php):
  - Specific for [VariableEventModifierConfig](./Entity/Config/VariableEventModifierConfig.php)
  - Modify the quantity variable of an VariableEventInterface event
  - Typical use cases: change the cost of an action - change the success rate of an action - increase or decrease the modification of a Daedalus or Player variable
- [MessageModifier](./ModifierHandler/MessageModifier.php):
  - Modify the content of a message
  - ModifierName should be set to the corresponding [effect](../Communication/Enum/MessageModificationEnum.php).


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

