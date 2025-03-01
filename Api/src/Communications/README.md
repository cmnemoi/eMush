# Communications

This modules handles Comms center game mechanics.

Communications Center is accessible mostly by the Comms Manager. It allows them to spend action points in diverse tasks to gain bonus :
- **Establish link with Sol** to gain an initial morale boost and unlocking other Comms Center task.
- **Contacting Xyloph** to gain equipment, bonuses for other Comms Center tasks or help for anti-Mush research and investigation.
- **Updating NERON version**. At each new version, an available NERON project is completed.
- **Decoding a rebel signal** to gain morale boost and bonuses through modifiers and new statuses.
- **Trading with merchants** to exchange inactive players against resources : new equipment or projects.

## Architecture

### Directory Tree:
    |-- config
    |-- ConfigData/DataFixtures
    |-- Entity
    |-- Enum
    |-- Event
    |-- Listener
    |-- Normalizer
    |-- Repository
    |-- Service
    |-- Dto
    |-- ValueObject

## How to add a new rebel base?

- Add the rebel base to the [RebelBaseEnum](./Enum/RebelBaseEnum.php)
- Add the rebel base to the [rebel_base_config_data.json](./ConfigData/rebel_base_config_data.json)
- Configure project modifiers in [ModifierConfigData](../Modifier/ConfigData/ModifierConfigData.php) and [RebelBaseModifierConfigFixtures](../Modifier/DataFixtures/RebelBaseModifierConfigFixtures.php). Please read the Modifier module [documentation](../Modifier/README.md) to see what can be implemented with modifiers, and how.
- Add the rebel base to [GameConfigData](../Game/ConfigData/GameConfigData.php)
- Add french translations in [rebel_base+intl-icu.fr.xlf](../../translations/fr/rebel_base+intl-icu.fr.xlf)