# Alert
This module track events in order to display alerts when
the Daedalus and her crew reach critical points.

# Architecture 

## Directory Tree:
    |-- config
    |-- Controller
    |-- Entity
    |-- Enum
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service

## Repositories

### Entity
- Alert entity stores:
  - The name of the alert
  - The Daedalus involved
  - In case of fires and broken equipments, a list of element is stored
- AlertElement: stores fires and broken equipment and whether they are reported or not
  - Equipment/Place: either the broken equipment of the place on fire
  - Player: is set if the equipment or fire is reported

