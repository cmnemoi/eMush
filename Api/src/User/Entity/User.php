<?php

declare(strict_types=1);

namespace Mush\User\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Achievement\Entity\PendingStatistics;
use Mush\Achievement\ValueObject\CycleCounts;
use Mush\MetaGame\Entity\Collection\ModerationSanctionCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Player\Entity\Player;
use Mush\User\Enum\RoleEnum;
use Mush\User\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'Users',
    description: 'eMush Users',
    normalizationContext: ['groups' => ['user_read']],
    denormalizationContext: ['groups' => ['user_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(
            filters: ['user.search_filter', 'user.order_filter'],
            security: 'is_granted("ROLE_MODERATOR")',
        ),
        new Get(
            security: 'is_granted("ROLE_USER") or object == user',
        ),
        new Patch(
            security: 'is_granted("ROLE_MODERATOR") and is_granted("EDIT_USER_ROLE", object)',
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[ApiProperty(identifier: false)]
    #[Groups(['player_info_read', 'user_read'])]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ApiProperty(identifier: true)]
    #[Groups(['player_info_read', 'user_read'])]
    private string $userId;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['player_info_read', 'user_read', 'user_write'])]
    private string $username;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isInGame = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nonceCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $nonceExpiryDate = null;

    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['player_info_read', 'user_read', 'user_write'])]
    private array $roles = [RoleEnum::USER];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ModerationSanction::class)]
    private Collection $moderationSanctions;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $hasAcceptedRules = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $hasReadLatestNews = false;

    #[ORM\Column(type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $lastActivityAt;

    #[ORM\Column(type: 'json', nullable: false, options: ['default' => '{}'])]
    private array $hashedIps = [];

    // deprecated
    #[ORM\Embedded(PendingStatistics::class, columnPrefix: false)]
    private PendingStatistics $pendingStatistics;

    public function __construct()
    {
        $this->moderationSanctions = new ArrayCollection();
        $this->lastActivityAt = new \DateTime();
        $this->pendingStatistics = new PendingStatistics();
    }

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

    public function isAdmin(): bool
    {
        return \in_array(RoleEnum::ADMIN, $this->roles, true) || \in_array(RoleEnum::SUPER_ADMIN, $this->roles, true);
    }

    public function isModerator(): bool
    {
        return \in_array(RoleEnum::MODERATOR, $this->roles, true) || $this->isAdmin();
    }

    public function isNotAdmin(): bool
    {
        return $this->isAdmin() === false;
    }

    public function getPassword(): null
    {
        return null;
    }

    public function eraseCredentials(): void {}

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

    #[Groups(['user_read'])]
    public function isBanned(): bool
    {
        return $this->getModerationSanctions()->isBanned();
    }

    public function getModerationSanctions(): ModerationSanctionCollection
    {
        return new ModerationSanctionCollection($this->moderationSanctions->toArray());
    }

    public function addModerationSanction(ModerationSanction $moderationAction): self
    {
        $this->moderationSanctions->add($moderationAction);

        return $this;
    }

    public function removeModerationSanction(ModerationSanction $moderationAction): self
    {
        $this->moderationSanctions->removeElement($moderationAction);

        return $this;
    }

    public function hasAcceptedRules(): bool
    {
        return $this->hasAcceptedRules;
    }

    public function acceptRules(): self
    {
        $this->hasAcceptedRules = true;

        return $this;
    }

    public function refuseRules(): self
    {
        $this->hasAcceptedRules = false;

        return $this;
    }

    public function readLatestNews(): self
    {
        $this->hasReadLatestNews = true;

        return $this;
    }

    public function markLatestNewsAsUnread(): self
    {
        $this->hasReadLatestNews = false;

        return $this;
    }

    public function hasNotReadLatestNews(): bool
    {
        return $this->hasReadLatestNews === false;
    }

    public function getLastActivityAt(): \DateTime
    {
        return $this->lastActivityAt;
    }

    public function updateLastActivityDate(): self
    {
        $this->lastActivityAt = new \DateTime();

        return $this;
    }

    /**
     * Return `True` if the user's last activity was before the given delta.
     *
     * @throws \DateMalformedStringException
     */
    public function lastActivityBefore(string $delta): bool
    {
        return $this->lastActivityAt <= new \DateTime($delta);
    }

    public function getHashedIps(): array
    {
        return $this->hashedIps;
    }

    public function addHashedIp(string $ip): self
    {
        if (!\in_array($ip, $this->hashedIps, true)) {
            $this->hashedIps[] = $ip;
        }

        return $this;
    }

    // deprecated
    public function incrementCyclesCount(Player $player, int $increment = 1): self
    {
        $this->pendingStatistics->incrementCyclesCountForPlayer($player, $increment);

        return $this;
    }

    // deprecated
    public function getCycleCounts(): CycleCounts
    {
        return $this->pendingStatistics->getCycleCounts();
    }

    #[Groups(['user_read'])]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    // deprecated
    public function resetCycleCounts(): self
    {
        $this->pendingStatistics->resetCycleCounts();

        return $this;
    }
}
