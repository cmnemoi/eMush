# Status
This module handle statuses.
Status are temporary additional information or effect on equipments, players and places.
Some statuses are visible by the players but other only serve game mechanics and are invisible.

# Architecture 

## Directory Tree:
    |-- ChargeStrategies
    |-- config
    |-- Criteria
    |-- CycleHandler
    |-- ConfigData/DataFixtures
    |-- DependencyInjection
    |-- Controller
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service

## Repositories
### Entity
- Status: contains the information about the status, its config, its owner and, optionally its target.
- ChargeStatus: extends status, store information about a charge (amount, max, what increase and decrease the charge...)
- Attempt: A special charge status used to handle the increase of success rate when failing an action.
- ContentStatus: A status to store document content
- StatusTarget: Link statuses with their owner and target (either equipment, player or places)

### ChargeStrategies
Charged status often increase or decrease on cycle or day change.
This directory contains the implementation of the different [strategies](./Enum/ChargeStrategyTypeEnum.php).

### CycleHandler
Some statuses have an effect on cycle changes. This directory implement the effects.

### ConfigData / DataFixtures
- Information needed to create a new status:
  - associated game config
  - name
  - [visibility](../Game/Enum/VisibilityEnum.php)
  - modifiers (optional) collection of [modifiers](../Modifier/README.md) added by the status.

- Information needed to create a new charge status:
  - associated game config
  - name
  - [visibility](../Game/Enum/VisibilityEnum.php)
  - charge visibility: some statuses are visible by player, but not the detail of the charge amount.
  - modifiers (optional) collection of [modifiers](../Modifier/README.md) added by the status.
  - charge strategy
  - starting charge
  - max charge
  - discharge strategy


