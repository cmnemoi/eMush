<?php

declare(strict_types=1);

namespace Mush\User\Factory;

use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Uid\Uuid;

abstract class UserFactory
{
    public static function createUser(): User
    {
        $user = new User();
        $user
            ->setUsername(Uuid::v4()->toRfc4122())
            ->setUserId(Uuid::v4()->toRfc4122());

        return $user;
    }

    public static function createSuperAdmin(): User
    {
        $user = self::createUser();
        $user->setRoles([RoleEnum::SUPER_ADMIN]);

        return $user;
    }

    public static function createAdmin(): User
    {
        $user = self::createUser();
        $user->setRoles([RoleEnum::ADMIN]);

        return $user;
    }

    public static function createModerator(): User
    {
        $user = self::createUser();
        $user->setRoles([RoleEnum::MODERATOR]);

        return $user;
    }
}