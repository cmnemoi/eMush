<?php

namespace Mush\User\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class User.
 *
 * @ORM\Entity(repositoryClass="Mush\User\Repository\UserRepository")
 * @ORM\Table(name="`user`")
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
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private string $userId;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $username;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Player\Entity\Player")
     */
    private ?Player $currentGame = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $nonceCode = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $nonceExpiryDate = null;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $roles = [RoleEnum::USER, RoleEnum::ADMIN];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
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

    public function getCurrentGame(): ?Player
    {
        return $this->currentGame;
    }

    public function setCurrentGame(?Player $currentGame): self
    {
        $this->currentGame = $currentGame;

        return $this;
    }

    public function getNonceCode(): ?string
    {
        return $this->nonceCode;
    }

    public function setNonceCode(?string $nonceCode): self
    {
        $this->nonceCode = $nonceCode;

        return $this;
    }

    public function getNonceExpiryDate(): ?DateTime
    {
        return $this->nonceExpiryDate;
    }

    public function setNonceExpiryDate(?DateTime $nonceExpiryDate): self
    {
        $this->nonceExpiryDate = $nonceExpiryDate;

        return $this;
    }
}
