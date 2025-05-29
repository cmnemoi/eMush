# Triumph

Triumph are points awarded to players based on specific game events. 

They are configured through the [`TriumphConfig`](./Entity/TriumphConfig.php) entity and can be awarded to different scopes of players.

## Triumph config Attributes

The [`TriumphConfig`](./Entity/TriumphConfig.php) entity contains the following relevant attributes:

- `name`: triumph name (from [`TriumphEnum`](./Enum/TriumphEnum.php))
- `scope`: Determines which players receive the triumph (from [`TriumphScope`](./Enum/TriumphScope.php))
- `targetedEvent`: The event that triggers this triumph
- `targetedEventExpectedTags`: Additional tags that must be present in the event for the triumph to be awarded (for the moment, all tags must be present for the triumph to apply - TODO)
- `target`: If set, only this character will receive the triumph. You can combine this `scope` to create more complex targeting conditions
- `quantity`: The amount of triumph points awarded
- `visibility`: Controls the visibility of triumph log
  - `PRIVATE`: Only visible to the player who earned it
  - `HIDDEN`: Not visible to players
- `regressiveFactor`: Determines after how many gains the gains has 2x less chance to be earned (TODO)

## Triumph Scopes

The [`TriumphScope`](./Enum/TriumphScope.php) enum defines which players receive a triumph:

- `ALL_ACTIVE_HUMANS`: All human players who are currently active in the game
- `ALL_ALIVE_MUSHS`: All Mush players who are still alive
- `ALL_ALIVE_HUMANS`: All human players who are still alive
- `ALL_MUSHS`: All Mush players

You can add new scopes to restrain your triumph targets.

## How to add a new triumph?

1. Add the triumph config to [TriumphConfigData](./ConfigData/TriumphConfigData.php)
2. If the triumph listens to a new event, it should:
   - implement [TriumphSourceEventInterface](./Event/TriumphSourceEventInterface.php)
   - some methods are already implemented in [TriumphSourceEventTrait](./Event/TriumphSourceEventTrait.php)
   - be added to [TriumphSourceEventSubscriber](./Listener/TriumphSourceEventSubscriber.php)
3. Add the triumph log french translation in [triumph+intl-icu.fr.xlf](../../translations/fr/triumph+intl-icu.fr.xlf)

### Concrete Example

Here is how to implement "Chun lives!" personal Chun's triumph (+1 triumph / day):

```php
new TriumphConfigDto(
    key: TriumphEnum::CHUN_LIVES->toConfigKey('default'),
    name: TriumphEnum::CHUN_LIVES,
    targetedEvent: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
    targetedEventExpectedTags: [
        EventEnum::NEW_DAY,
    ],
    scope: TriumphScope::PERSONAL,
    target: CharacterEnum::CHUN,
    quantity: 1,
)
```

This triumph:
- Is triggered on each new cycle of the Daedalus (event: `DAEDALUS_NEW_CYCLE`)
- Only applies if the event has the tag `NEW_DAY`
- Uses the `PERSONAL` scope and `target` to award it to Chun only

