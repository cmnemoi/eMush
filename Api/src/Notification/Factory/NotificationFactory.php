<?php

declare(strict_types=1);

namespace Mush\Notification\Factory;

use Mush\Game\Service\TranslationServiceInterface as Translate;
use Mush\Notification\Command\NotifyUserCommand;
use Mush\Notification\Enum\NotificationEnum;
use Mush\User\Entity\User;
use WebPush\Action;
use WebPush\Message as WebPushMessage;
use WebPush\Notification as WebPushNotification;

abstract class NotificationFactory
{
    public static function createFromCommand(NotifyUserCommand $command, Translate $translate): WebPushNotification
    {
        return match ($command->priority) {
            WebPushNotification::URGENCY_HIGH => self::createUrgentForUser($command->notification, $command->user, $command->language, $command->translationParameters, $translate),
            default => self::createForUser($command->notification, $command->user, $command->language, $command->translationParameters, $translate),
        };
    }

    private static function createForUser(NotificationEnum $name, User $user, string $language, array $translationParameters, Translate $translate): WebPushNotification
    {
        $message = self::createMessageForUser($name, $user, $language, $translationParameters, $translate)
            ->addAction(Action::create('ok', self::translateForUser('ok', $language, $user, $translationParameters, $translate)));

        $message = $message->withData(array_merge($message->getData(), ['priority' => WebPushNotification::URGENCY_NORMAL]));

        return new WebPushNotification()->withPayload($message->toString())->withTTL(3 * 60 * 60)->withUrgency(WebPushNotification::URGENCY_NORMAL);
    }

    private static function createUrgentForUser(NotificationEnum $name, User $user, string $language, array $translationParameters, Translate $translate): WebPushNotification
    {
        $message = self::createMessageForUser($name, $user, $language, $translationParameters, $translate)->vibrate(200, 300, 200, 300)
            ->addAction(Action::create('go', self::translateForUser('go', $language, $user, $translationParameters, $translate)))
            ->addAction(Action::create('later', self::translateForUser('later', $language, $user, $translationParameters, $translate)));

        $message = $message->withData(array_merge($message->getData(), ['priority' => WebPushNotification::URGENCY_HIGH]));

        return new WebPushNotification()->withPayload($message->toString())->withTTL(30 * 60)->withUrgency(WebPushNotification::URGENCY_HIGH);
    }

    private static function createMessageForUser(NotificationEnum $name, User $user, string $language, array $translationParameters, Translate $translate): WebPushMessage
    {
        return WebPushMessage::create(
            title: self::translateForUser($name->toTranslationTitleKey(), $language, $user, $translationParameters, $translate),
            body: self::translateForUser($name->toTranslationBodyKey(), $language, $user, $translationParameters, $translate)
        )
            ->auto()
            ->renotify()
            ->withImage('/twitter-card.png')
            ->withIcon('/pwa-192x192.png')
            ->withBadge('/pwa-192x192-monochrome.png')
            ->withData(['link' => '/'])
            ->withTag($name->toString())
            ->withLang($language)
            ->withTimestamp(new \DateTime()->getTimestamp() * 1_000)
            ->interactionRequired();
    }

    private static function translateForUser(string $key, string $language, User $user, array $translationParameters, Translate $translate): string
    {
        return $translate(
            $key,
            array_merge(['user' => $user->getUsername()], $translationParameters),
            'user_notification',
            $language
        );
    }
}
