<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\TimestampableCancelInterface;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

#[ORM\Entity]
class Message implements TimestampableCancelInterface, SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PlayerInfo::class)]
    private ?PlayerInfo $author = null;

    #[ORM\ManyToOne(targetEntity: Neron::class)]
    private ?Neron $neron = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, orphanRemoval: true, fetch: 'EAGER')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $child;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'child')]
    private ?Message $parent = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'messages')]
    private Channel $channel;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(type: 'array', nullable: true)]
    private array $translationParameters = [];

    #[ORM\ManyToMany(targetEntity: Player::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'message_readers')]
    private Collection $readers;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'favoriteMessages', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'message_favorites')]
    private Collection $favorites;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $timestampableCanceled = false;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $day = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $cycle = 0;

    public function __construct()
    {
        $this->child = new ArrayCollection();
        $this->readers = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?PlayerInfo
    {
        return $this->author;
    }

    public function setAuthor(?PlayerInfo $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthorAsPlayerOrThrow(): Player
    {
        $author = $this->getAuthor()?->getPlayer();

        return $author instanceof Player ? $author : throw new \RuntimeException('Message does not have an author');
    }

    public function getNeron(): ?Neron
    {
        return $this->neron;
    }

    public function setNeron(?Neron $neron): self
    {
        $this->neron = $neron;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        if ($parent !== null) {
            $parent->addChild($this);
        }

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isNotInMushChannel(): bool
    {
        return $this->channel->isMushChannel() === false;
    }

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }

    public function setTranslationParameters(array $translationParameters): static
    {
        $this->translationParameters = $translationParameters;

        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function isInMushChannel(): bool
    {
        return $this->channel->isMushChannel();
    }

    public function getChild(): Collection
    {
        return $this->child;
    }

    public function setChild(Collection $child): static
    {
        $this->child = $child;

        return $this;
    }

    public function addChild(self $child): static
    {
        if ($this->child->contains($child)) {
            return $this;
        }

        $this->child->add($child);

        return $this;
    }

    public function addReader(Player $reader): static
    {
        if (!$this->readers->contains($reader)) {
            $this->readers->add($reader);
        }

        return $this;
    }

    public function isUnreadBy(Player $player): bool
    {
        return !$this->readers->contains($player);
    }

    public function isReadBy(Player $player): bool
    {
        return $this->readers->contains($player);
    }

    public function addFavorite(Player $player): static
    {
        if (!$this->favorites->contains($player)) {
            $this->favorites->add($player);
        }

        return $this;
    }

    public function removeFavorite(Player $player): static
    {
        $this->favorites->removeElement($player);

        return $this;
    }

    public function isFavoriteFor(Player $player): bool
    {
        return $this->favorites->contains($player);
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function isTimestampableCanceled(): bool
    {
        return $this->timestampableCanceled;
    }

    public function cancelTimestampable(): static
    {
        $this->timestampableCanceled = true;

        return $this;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getCreatedAtOrThrow(): \DateTime
    {
        $createdAt = $this->getCreatedAt();
        if (!$createdAt) {
            throw new \RuntimeException('Message should have a createdAt date');
        }

        return $createdAt;
    }
}
