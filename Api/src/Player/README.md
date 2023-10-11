# Player
This module handle the player entities
Player entities represent the crew of the Daedalus.
They store the state of the character (health, morale, action...)

# Architecture

## Directory Tree:
    |-- config
    |-- Controller
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- ParamConverter
    |-- Repository
    |-- Service
    |-- Validator
    |-- Voter



## Entity
- Player
  - user: user that control this character
  - [gameStatus](../Game/Enum/GameStatusEnum.php)
  - characterConfig
  - 6 variables:
    - health
    - morale
    - action
    - movement
    - satiety
    - triumph
  - place of the character
  - Daedalus of the character
  - items: collection of items present in player inventory
  - Collection of status, modifier, medicalConditions
  - flirts: collection of player this player flirted with
  - Titles of the character
- DeadPlayerInfo: add information on death
  - cause
  - time
  - end message

## Event
- PlayerCycleEvent: all the events that occurs at cycle change
- PlayerEvent:
  - player death
  - new player
  - Contamination by a spore
  - Conversion to mush
  - Metal plate and panic crisis event
  - player end (after death validation)
- PlayerVariableEvent: events that modify player variables (health, morale, movement, action)

## ConfigData / DataFixtures
When creating a new character, you need to specify:
- name
- initial statuses: all characters have a spore status by default
- available actions
- skills (@TODO)




