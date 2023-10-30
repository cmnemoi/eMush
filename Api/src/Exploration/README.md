# Exploration
This module handles explorations gameplay. 

It is composed of two main parts: looking for new planets and the exploration of these planets itself.

For an exhaustive list of features introduced by this module, please refer to the [0.7 Milestone page](https://gitlab.com/eternaltwin/mush/mush/-/milestones/17#tab-issues).

# Data model: 

![exploration data model](https://gitlab.com/eternaltwin/mush/mush/uploads/ce736c35739eb63002d2ee3da19f7d84/Planets.drawio_3_.svg)

(To complete)

# Directory Tree:
    |-- config
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Normalizer
    |-- Repository
    |-- Service

# Repositories

## ConfigData / DataFixtures
- [PlanetSectorConfigData](./ConfigData/PlanetSectorConfigData.php) contains the list of all sectors that can be found on a planet (and their associated discovering/visiting probabilities). It also contains the `ExplorationEvent`s that can be triggered when a player visits a sector.
If you want to add a new sector, you need to do it here.

## Entity
- [Planet](./Entity/Planet.php) represents a planet. A `Planet` has an orientation and a distance which are used at `Daedalus` travel to check if the `Daedalus` is in orbit of the planet. It also has a list of `PlanetSector`s that can be explored by players. A `Planet` is scanned by `player` which is the only one who can analyze it, unless the `Daedalus` is in orbit of the planet.
- [PlanetSector](./Entity/PlanetSector.php) represents a sector of a planet. It can be `revealed` by planet analysis or `explored` by an exploration (not doable again). It has a [PlanetSectorConfig](./Entity/Config/PlanetSectorConfig.php) which contains the list of `ExplorationEvent`s that can be triggered when a player visits the sector.

## Event
- [ExplorationEvent](./Event/ExplorationEvent.php) is triggered when a player visits a [PlanetSector](./Entity/PlanetSector.php). Each sector has a specific pool of events. Examples : fighting a creature, losing HP from an accident or finding an artifact. TODO: Each event has a name and a `outputQuantityTable`. The `outputQuantityTable` will be used to compute the output of the event (e.g. the amount of HP lost when the player has an accident).

## Service
- [PlanetService](./Service/PlanetService.php) contains the logic to generate a new planet and its sectors, and to delete a planet.

### Planet generation

- Loop through the number of slots available on the planet
- For each slot, generate a random number within a range that is determined by the total weight of all sector configurations.
- Iterate over all possible sector configurations and accumulate the weights of these sectors until it surpasses the random number generated.
- Once the accumulated weight exceeds the random number, select and add the corresponding PlanetSector to the planet and break the loop.
- If a sector reaches its maximum limit allowed on the planet, remove it from the available sector configurations, and substract its weight from the total weight.