# Action
This module handle the actions executed by the player. If applies effects and compute what actons are availlable to the player.

# Architecture 

## Directory Tree:
    |-- Actions
    |-- config
    |-- Controller
    |-- ConfigData/DataFixtures
    |-- DependencyIjection
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service
    |-- Validator

## Repositories

### Actions
Contain a file for every available action, each action being its own class.
All action classes extend [AbstractAction](./Actions/AbstractAction.php).
Actions with a success rate extend [AttemptAction](./Actions/AttemptAction.php).

### ConfigData / DataFixtures
- Action cost are initialized in [ActionCostFixtures](./DataFixtures/ActionCostFixture.php).
- Actions are initialized in [ActionsFixtures](./DataFixtures/ActionsFixtures.php).
  - name: the key of the action
  - actionCost: the action cost
  - injuryRate: chances to get hurt doing the action
  - dirtyRate: chances to get dirty
  - successRate: if necessary
  - [scope](./Enum/ActionScopeEnum.php): the relation between the active player and the entity that provide the action.
  - target: (only apply on action provided by tools) describe which entity is targeted by the tool.

### Event
- Each action trigger 3 events: PRE, POST and RESULT
- Moreover, some actions require additional events to be triggered
  - Should we rework the module such as effect are dispatched as events?

### Validator
This folder contains all the check required before showing the action to the player or letting him perform the action.


### Actions vs Events

The difference between an action and an event (from a development perspective) is an Action is what a User want to do.
An event is something that can be the result of an action, or the change of cycle.
Obvious example:
- Action:
    - Move: The player use a door
    - Shoot a hunter
- Event:
    - The cycle change
    - A player die
    
 For instance a player can make the action 'hit' on an other player, this will trigger the event 'player die'.  
 Less obvious example:   
    A player make the action eat, that trigger the event 'become Dirty'

# Create a new Action:
- Create a class that extends [AbstractAction](./Actions/AbstractAction.php) in [Actions directory](./Actions).
- Register this action in the [ActionEnum](./src/Action/Enum/ActionEnum.php)
- Add the [action fixture](./src/Action/DataFixtures/ActionsFixtures.php) (cost, name, injury rate...)
- Add the action to the associated equipment or player respectively in [equipment fixtures](./src/Equipment/DataFixtures) or [character config fixtures](./src/Player/DataFixtures/CharacterConfigFixtures.php).