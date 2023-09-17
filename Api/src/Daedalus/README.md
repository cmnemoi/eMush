# Daedalus
This module handle the communication between player.
It also includes services to build NERON messages

# Architecture 

## Directory Tree:
    |-- config
    |-- Controller
    |-- Entity
    |-- Enum
    |-- Listener
    |-- Normalizer
    |-- ParamConverter
    |-- Repository
    |-- Service
    |-- Specification
    |-- Validator
    |-- Voter

## Repositories

### Daedalus' entity:
  - players: a collection of players present in this ship
  - gameConfig: all the initialization information for this Daedalus
  - neron: the NERON entity of this Daedalus
  - [gameStatus](../Game/Enum/GameStatusEnum.php)
  - places: a collection of places composing the Daedalus
  - modifiers: a collection of modifiers that apply to this Daedalus
  - oxygen, fuel, hull, shield: int storing the current state of the Daedalus
  - spores, dailySpores: tracks the number of spores extracted on the current day and the daily limit
  - day, cycle: store the game time in day and cycles
  - filledAt: store the time when the game started (end of the lobby)
  - finishedAt: store the time of the end of the game
  - cycleStartedAt: store the time of the beginning of the first cycle
  - isCycleChange: set to true during cycle change to avoid other events to occurs during this lapse of time

### DaedalusConfig
- You can alter several parameters in the [fixtures](./DataFixtures/DaedalusConfigFixtures.php)
  - initial amount of hull, shield, oxygen and fuel
  - maximum amount of hull, shield, oxygen and fuel
  - Spore maximum amount per day
  - Random items to distributes among chosen room at the beginning of the game

