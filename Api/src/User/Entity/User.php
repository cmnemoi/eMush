<?php

namespace Mush\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
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

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isInGame = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nonceCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $nonceExpiryDate = null;

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

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::ADMIN) || $this->hasRole(RoleEnum::SUPER_ADMIN);
    }

    public function isModerator(): bool
    {
        return $this->hasRole(RoleEnum::MODERATOR) || $this->isAdmin();
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

    public function isInGame(): bool
    {
        return $this->isInGame;
    }

    public function startGame(): self
    {
        $this->isInGame = true;

        return $this;
    }

    public function stopGame(): self
    {
        $this->isInGame = false;

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

    public function getNonceExpiryDate(): ?\DateTime
    {
        return $this->nonceExpiryDate;
    }

    public function setNonceExpiryDate(?\DateTime $nonceExpiryDate): self
    {
        $this->nonceExpiryDate = $nonceExpiryDate;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->userId;
    }
}
