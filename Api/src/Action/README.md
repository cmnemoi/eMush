# Action
This module handle the actions executed by the player. If applies effects and compute what actons are available to the player.

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
- Actions are initialized in [ActionData](./ConfigData/ActionData.php) (and in [ActionsFixtures](./DataFixtures/ActionsFixtures.php) for tests).
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
    
 For instance a player can make the action 'hit' on another player, this will trigger the event 'player die'.  
 Less obvious example:   
    A player make the action eat, that trigger the event 'become Dirty'

# Create a new Action:
- The list of actions that need to be added can be found on [git](https://gitlab.com/eternaltwin/mush/mush/-/issues/396). 
- Create a class that extends [AbstractAction](./Actions/AbstractAction.php) in [Actions directory](./Actions).
- Register the action name in the [ActionEnum](./Enum/ActionEnum.php)
- Add the [action data](./ConfigData/ActionData.php) (cost, name, injury rate...) and fixtures in  [ActionsFixtures](./DataFixtures/ActionsFixtures.php).
- Add the action to the associated equipment or player respectively in [equipment data](../Equipment/ConfigData/EquipmentConfigData.php) or [character config data](../Player/ConfigData/CharacterConfigData.php).
- Setup action log visibility in [ActionLogEnum](../RoomLog/Enum/ActionLogEnum.php)
- Add French translations in [actions+intl-icu.fr.xlf](../../translations/fr/actions+intl-icu.fr.xlf), [actions_log+intl-icu.fr.xlf](../../translations/fr/actions_log+intl-icu.fr.xlf) (and [action_fail+intl-icu.fr.xlf](../../translations/fr/action_fail+intl-icu.fr.xlf) if needed).