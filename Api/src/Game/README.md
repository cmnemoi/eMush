# Game
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


## Services
- Cycle service
  - compute days and cycles
  - compute elapsed time since last update
  - trigger the cycle change event
- Random service: custom random service
- Translation service
  - add additional parameters to the log depending on the language
  - translate the log parameters


## DataFixtures
Contains various information for ship initialization:
- DifficultyConfigFixture: control the overall difficulty with rates of event and intensity of consequences.
- GameConfigFixtures: store information on starting and max player variables.
- TriumphConfigFixtures: store the triumph gain of each action for human and mush