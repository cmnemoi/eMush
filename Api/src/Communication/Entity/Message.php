<?php

namespace Mush\Communication\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Neron;
use Mush\Player\Entity\PlayerInfo;

#[ORM\Entity]
class Message
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

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Message::class)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $child;

    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'child')]
    private ?Message $parent = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'messages')]
    private Channel $channel;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(type: 'array', nullable: true)]
    private array $translationParameters = [];

    public function __construct()
    {
        $this->child = new ArrayCollection();
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

    public function getNeron(): ?Neron
    {
        return $this->neron;
    }

    public function setNeron(?Neron $neron): self
    {
        $this->neron = $neron;

        return $this;
    }

    public function getParent(): ?Message
    {
        return $this->parent;
    }

    public function setParent(?Message $parent): static
    {
        $this->parent = $parent;

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

    public function getChild(): Collection
    {
        return $this->child;
    }

    public function setChild(Collection $child): static
    {
        $this->child = $child;

        return $this;
    }
}
