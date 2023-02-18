# Place
This module handle the places.
Daedalus is composed of different places.
Those can be room but also space or planets

# Architecture 

## Directory Tree:
    |-- config
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service

## Repositories

### Entity
- place:
  - type: is the place a room?
  - contains collections of player, equipment, statuses and modifiers
- placeConfig: initialization of the place
  - equipments that are present on Daedalus initialization


