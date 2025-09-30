# Achievement

This module handles user achievements and statistics tracking.

The achievement system works in two parts: **Statistics** track user actions (like planets scanned, number of cycles played with a character, etc.), and **Achievements** are unlocked when statistics reach certain thresholds.

## Architecture

### Key Components:

#### Entities
- **Entity/Statistic.php**: Tracks individual user statistics with counts
- **Entity/StatisticConfig.php**: Defines available statistics configuration
- **Entity/Achievement.php**: Represents unlocked achievements for users
- **Entity/AchievementConfig.php**: Defines achievement configurations and unlock thresholds

#### Commands
- **Command/IncrementUserStatisticCommandHandler.php**: Processes statistic increments and triggers events
- **Command/UnlockStatisticAchievementCommandHandler.php**: Handles achievement unlocking logic

#### Event System
- **Event/StatisticIncrementedEvent.php**: Fired when a statistic is incremented
- **Listener/StatisticEventSubscriber.php**: Listens for statistic events to trigger achievement checks
- **Listener/PlanetEventSubscriber.php**: Example listener for game events that increment statistics

#### Enums
- **Enum/StatisticEnum.php**: Defines all available statistics (e.g., PLANET_SCANNED)
- **Enum/AchievementEnum.php**: Defines all available achievements (e.g., PLANET_SCANNED_1)

## How the Achievement System Works

### Flow Overview:
1. **Game Event Occurs**: A player performs an action (e.g., scans a planet)
2. **Event Listener Triggers**: Event listener dispatches `IncrementUserStatisticCommand`
3. **Statistic Updated**: The user's statistic count is incremented
4. **Achievement Check**: `StatisticIncrementedEvent` is fired, triggering achievement verification
5. **Achievement Unlocked**: If thresholds are met, achievements are unlocked for the user

## How to add new achievements

### 1. Add a new statistic type

Add the new statistic to [StatisticEnum](./Enum/StatisticEnum.php):

```php
enum StatisticEnum: string
{
    case PLANET_SCANNED = 'planet_scanned';
    case YOUR_NEW_STATISTIC = 'your_new_statistic';
    // ...existing code...
}
```

### 2. Add corresponding achievements

Add achievement milestones to [AchievementEnum](./Enum/AchievementEnum.php):

```php
enum AchievementEnum: string
{
    case PLANET_SCANNED_1 = 'planet_scanned_1';
    case YOUR_NEW_STATISTIC_1 = 'your_new_statistic_1';
    case YOUR_NEW_STATISTIC_10 = 'your_new_statistic_10';
    case YOUR_NEW_STATISTIC_100 = 'your_new_statistic_100';
    // ...existing code...
}
```

### 3. Add translations

Add translations for both statistics and achievements in the translation files:

**For statistics** in `translations/[language]/statistics+intl-icu.[language].xlf`:
```xml
<unit id="your_new_statistic.name">
  <segment state="translated">
    <source>your_new_statistic.name</source>
    <target>Your Statistic Display Name</target>
  </segment>
</unit>
<unit id="your_new_statistic.description">
  <segment state="translated">
    <source>your_new_statistic.description</source>
    <target>Description of what this statistic tracks.</target>
  </segment>
</unit>
```

**For achievements** in the same file:
```xml
<unit id="your_new_statistic_1.name">
  <segment state="translated">
    <source>your_new_statistic_1.name</source>
    <target>Achievement Name</target>
  </segment>
</unit>
```

### 4. Add configuration data

Add the new statistic and achievements to the configuration data files:

**In [ConfigData/StatisticConfigData.php](./ConfigData/StatisticConfigData.php)**, add to the `getAll()` method:

```php
public static function getAll(): array
{
    return [
        new StatisticConfigDto(
            name: StatisticEnum::PLANET_SCANNED,
        ),
        new StatisticConfigDto(
            name: StatisticEnum::YOUR_NEW_STATISTIC,
            isRare: true, // optional, defaults to false
        ),
        // ...existing code...
    ];
}
```

**In [ConfigData/AchievementConfigData.php](./ConfigData/AchievementConfigData.php)**, add to the `getAll()` method:

```php
public static function getAll(): array
{
    return [
        new AchievementConfigDto(
            name: AchievementEnum::PLANET_SCANNED_1,
            points: 1,
            threshold: 1,
        ),
        new AchievementConfigDto(
            name: AchievementEnum::YOUR_NEW_STATISTIC_1,
            points: 1,
            threshold: 1,
        ),
        new AchievementConfigDto(
            name: AchievementEnum::YOUR_NEW_STATISTIC_10,
            points: 2,
            threshold: 10,
        ),
        // ...existing code...
    ];
}
```

### 5. Listen to game events and increment statistics

Create an event listener or add to an existing one that listens to your event of interest, and dispatch the statistic increment with the command bus:

```php
final readonly class YourEventListener implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            YourGameEvent::class => 'onYourGameEvent',
        ];
    }

    public function onYourGameEvent(YourGameEvent $event): void
    {
        // Your logic to determine which user performed the action
        $userId = ...;
        // Your logic to determine the language of the user
        $language = ...; // exemple : $event->getDaedalus()->getLanguage()
        
        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $userId,
                statisticName: StatisticEnum::YOUR_NEW_STATISTIC,
                language: $language,
            )
        );
    }
}
```

## API Endpoints

The module provides API endpoints through [Controller/AchievementController.php](./Controller/AchievementController.php):

- **GET /api/v1/statistics?userId={id}&language={language}**: Retrieve all statistics for a user
- **GET /api/v1/achievements?userId={id}&language={language}**: Retrieve all achievements for a user

## Testing

Write functional tests that:
1. Trigger the game events that should increment statistics
2. Assert that statistics are correctly incremented
3. Assert that achievements are unlocked when thresholds are reached

Example test structure:
```php
public function shouldIncrementStatisticWhenEventOccurs(FunctionalTester $I): void
{
    // Given a user and initial state
    // When the game event occurs
    // Then the statistic should be incremented
    // And achievements should be unlocked if thresholds are met
}
```

You can find an example in [ScanCest::shouldIncrementUserStatisticOnSuccess](../../tests/functional/Action/Actions/ScanCest.php#L344)

