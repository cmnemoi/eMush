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
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service


## Listener
Listen to events that cause and heal medical conditions.

## Entity
- ConsumableDisease: 1 per daedalus, store ConsumableDiseaseAttributes
- ConsumableDiseaseAttribute: consumables can cause diseases. For each disease that can be caused by a consumable, a ConsumableDiseaseAttribute store the chance to get the disease, the incubation delays and the name of the disease.
- PlayerDisease: this entity stores every disease a player currently have (incubating and active).
- Config
  - DiseaseConfig: each disease have a unique DiseaseConfig that describe how the disease affect the player (modifiers, symptoms)
  - ConsumableDiseaseConfig: some consumables give and cure random diseases. This entity stores the possible cure and disease consumable can have.
  - DiseaseCauseConfig: provides information on which disease can occur for different causes. One per daedalus and disease cause.
