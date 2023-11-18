# Exploration
This module handles explorations gameplay. 

It is composed of two main parts: looking for new planets and the exploration of these planets itself.

For an exhaustive list of features introduced by this module, please refer to the [0.7 Milestone page](https://gitlab.com/eternaltwin/mush/mush/-/milestones/17#tab-issues).

# Data models: 

![planet data model](https://gitlab.com/eternaltwin/mush/mush/uploads/ce736c35739eb63002d2ee3da19f7d84/Planets.drawio_3_.svg)

![exploration data model](https://cdn.discordapp.com/attachments/1165002647095496764/1165261833133903932/Screenshot_from_2023-10-21_14-14-21.png?ex=6558aaa6&is=654635a6&hm=b1b472d0225cdf9d64a7adb9c6b89a1ce2b988375e50fce6b6406b2c16d40ed5&)


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

## ConfigData
- [PlanetSectorConfigData](./ConfigData/PlanetSectorConfigData.php) contains the list of all sectors that can be found on a planet (and their associated discovering/visiting probabilities). It also contains the `ExplorationEvent`s that can be triggered when a player visits a sector.
If you want to add a new sector, you need to do it here.
To add the events associated with the sector, go to [EventConfigData](../Game/ConfigData/EventConfigData.php).

## Entity
- [Planet](./Entity/Planet.php) represents a planet. A `Planet` has an orientation and a distance which are used at `Daedalus` travel to check if the `Daedalus` is in orbit of the planet. It also has a list of `PlanetSector`s that can be explored by players. A `Planet` is scanned by `player` which is the only one who can analyze it, unless the `Daedalus` is in orbit of the planet.
- [PlanetSector](./Entity/PlanetSector.php) represents a sector of a planet. It can be `revealed` by planet analysis or `explored` by an exploration (not doabl8e again). It has a [PlanetSectorConfig](./Entity/Config/PlanetSectorConfig.php) which contains the list of `ExplorationEvent`s that can be triggered when a player visits the sector.
- [Exploration](./Entity/Exploration.php) represents an exploration. An exploration needs a `planet` and `explorators` to be created. Those explorators visit the planet for `numberOfSectionsToVisit` steps, defined by the action used to launch the exploration (9 for Icarus, 3 for patrol ships). Current step is stored in a `cycle` to dispatch `ExplorationEvent`s at each step with a similar logic to Daedalus cycle change.
- [ClosedExploration](./Entity/ClosedExploration.php) represents a finished exploration. The implementation and purpose is similar to [`ClosedDaedalus`](../Daedalus/Entity/ClosedDaedalus.php). It contains the list of [ExplorationLog](./Entity/ExplorationLog.php)s and useful info to be accessed after the exploration is finished.

## Event
- [ExplorationEvent](./Event/ExplorationEvent.php) are dispatched to handle general exploration events. The more important are exploration started, finished and new cycle.
- [PlanetSectorEvent](./Event/PlanetSectorEvent.php) are dispatched when explorators visit a `PlanetSector`. Each sector has a specific pool of events. Examples : fighting a creature, losing HP from an accident or finding an artifact. Each event has a name and a `outputQuantityTable`. The `outputQuantityTable` will be used to compute the output of the event (e.g. the amount of HP lost when the player has an accident).

## Service
- [PlanetService](./Service/PlanetService.php) contains the logic to generate a new planet and its sectors, and to delete a planet.
- [ExplorationService](./Service/ExplorationService.php) contains the logic to create, and dispatch exploration events and close an exploration.

### Planet generation

- Loop through the number of slots available on the planet
- For each slot, generate a random number within a range that is determined by the total weight of all sector configurations.
- Iterate over all possible sector configurations and accumulate the weights of these sectors until it surpasses the random number generated.
- Once the accumulated weight exceeds the random number, select and add the corresponding PlanetSector to the planet and break the loop.
- If a sector reaches its maximum limit allowed on the planet, remove it from the available sector configurations, and substract its weight from the total weight.

### Exploration cycle changes

Similarly to Daedalus cycle changes, we dispatch new Exploration events this way :

- Every 1/18 of a Daedalus cycle (10 minutes for 3-hours cycles), dispatch `ExplorationEvent::EXPLORATION_NEW_CYCLE`
- Listen to `ExplorationEvent::EXPLORATION_NEW_CYCLE` in a subscriber and use `ExplorationService::dispatchExplorationEvent` to select a random `PlanetSector` then a random `PlanetSectorEvent` from the sector's event pool and dispatch it.
- Listen this `PlanetSectorEvent` in a subscriber and use the relevant service to handle it (create an equipment, removing health points, etc.).

This is handled in [`CycleService`](../Game/Service/CycleService.php).

## Voter

`ClosedExploration` is accessible if and only if :
- The Daedalus is finished
- The player participates in the exploration
- The exploration is finished and player is in the Daedalus associated with the exploration

This is handled by [`ClosedDaedalusVoter`](./Voter/ClosedExplorationVoter.php).