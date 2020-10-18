<?php

namespace Mush\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\User\Repository\UserRepository")
 */
class User implements UserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $userId;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $username;

    private Player $currentGame;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): User
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function getRoles()
    {
        return [RoleEnum::USER];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }

    public function getCurrentGame(): Player
    {
        return $this->currentGame;
    }

    public function setCurrentGame(Player $currentGame): User
    {
        $this->currentGame = $currentGame;
        return $this;
    }
}