<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'personal_notes_tab')]
class PersonalNotesTab
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: PersonalNotes::class, inversedBy: 'tabs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PersonalNotes $personalNotes;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $index;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $icon;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $content;

    public function __construct(PersonalNotes $personalNotes, ?string $icon = null, ?string $content = '', int $index = 15)
    {
        $this->personalNotes = $personalNotes;
        $this->content = $content ?? '';
        $this->icon = $icon;
        $this->index = $index;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPersonalNotes(): PersonalNotes
    {
        return $this->personalNotes;
    }

    public function setPersonalNotes(PersonalNotes $personalNotes): self
    {
        $this->personalNotes = $personalNotes;

        return $this;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getContent(): string
    {
        return $this->personalNotes->getPlayer()->canReachATalkie() ? $this->content : '';
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
