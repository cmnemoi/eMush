<?php

namespace Mush\User\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Enum\RoleEnum;
use Mush\User\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $userId;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $username;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: PlayerInfo::class)]
    private ?PlayerInfo $playerInfo = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nonceCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $nonceExpiryDate = null;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $roles = [RoleEnum::USER];

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

    public function getRoles(): array
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

    public function getPlayerInfo(): ?PlayerInfo
    {
        return $this->playerInfo;
    }

    public function setPlayerInfo(?PlayerInfo $playerInfo): self
    {
        $this->playerInfo = $playerInfo;

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

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }
}
