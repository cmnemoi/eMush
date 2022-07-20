# Disease
This module handle medical conditions.

Medical conditions apply to player they come with negative effects.
Those effects are handled by [modifiers](../Modifier/README.md).

There is three types of medical conditions:
- Diseases: (gave the name to the module)
  - healed by drugs, fruit and heal actions
  - caused by alien fruits
- Disorders:
  - healed by shrink and drugs or fruit consumption
  - caused by alien fruit and trauma
- Injury
  - caused by fights
  - healed on the surgical plot

# Architecture 

## Directory Tree:
    |-- config
    |-- DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service


## Listener
Listen to events that cause and heal medical conditions.
