# Notification

This module handles web push notifications to users. It allows the game to send real-time notifications to users about important events happening in their game, even when they are not actively browsing the application.

The notification system uses the [Web Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API) to deliver notifications directly to users' browsers, providing an engaging way to keep users informed about game events such as inactivity warnings, game updates, and other important information.

![Notification Flow](https://gitlab.com/-/project/19328996/uploads/244d000810fd5574ce06ad968249dc96/push_notifications.webp)

## Architecture

### Key Components:

- **Entity/Subscription.php**: Stores user subscription data for web push notifications
- **Command/NotifyUserCommand.php**: Command to trigger notification sending
- **Command/NotifyUserCommandHandler.php**: Dispatches the notification
- **Factory/NotificationFactory.php**: Creates web push notifications with proper formatting
- **Listener/StatusEventListener.php**: Example listener that responds to game events to send notifications
- **Enum/NotificationEnum.php**: Defines different types of notifications

## How to dispatch notifications

### 1. Add a new notification type

Add the new notification type to the [NotificationEnum](./Enum/NotificationEnum.php):

```php
enum NotificationEnum: string
{
    // ...existing code...
    case NEW_NOTIFICATION_TYPE = 'new_notification_type';
}
```

### 2. Add French translation

Add the notification message to [user_notification+intl-icu.fr.xlf](../../translations/fr/user_notification+intl-icu.fr.xlf):

```xml
<unit id="new_notification_type">
  <segment state="translated">
    <source>new_notification_type</source>
    <target>Your translated message here with {user} parameter if needed</target>
  </segment>
</unit>
```

### 3. Listen to an event and dispatch notification

Create an event listener or add to an existing one that listens to your event of interest, and dispatch the notification using the command bus:

```php
final readonly class YourEventListener implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            YourEvent::class => 'onYourEvent',
        ];
    }

    public function onYourEvent(YourEvent $event): void
    {
        // Your logic to determine if notification should be sent
        
        $this->commandBus->dispatch(
            new NotifyUserCommand(
                notification: NotificationEnum::NEW_NOTIFICATION_TYPE,
                user: $targetUser,
                language: $targetUser->getLanguage(),
                // priority: Notification::URGENCY_HIGH - optional
            )
        );
    }
}
```

# Testing

## Automated

Write a functional test that triggers your event of interest, and then assert that the notification was sent.

You have an example of this [here](../../tests/functional/Notification/Event/InactivityStatusAppliedCest.php).

## Manual

After subscribing to notifications in the app, you can send yourself a test notification by calling the `api/v1/notifications/notify` endpoint.
The best way to do this for now is to use the [OpenAPI](http://localhost:8080/swagger) documentation page.

[How to use the OpenAPI documentation page](../../../README.md#openapi--swagger-documentation-page)