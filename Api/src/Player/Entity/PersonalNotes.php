<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'personal_notes')]
class PersonalNotes
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'personalNotes', targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\OneToMany(mappedBy: 'personalNotes', targetEntity: PersonalNotesTab::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['index' => Order::Ascending->value])]
    private Collection $tabs;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->player->setPersonalNotes($this);
        $this->tabs = new ArrayCollection([new PersonalNotesTab($this)]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Collection<int, PersonalNotesTab>
     */
    public function getTabs(): Collection
    {
        return $this->tabs;
    }

    public function getTabFromId(int $id): ?PersonalNotesTab
    {
        return $this->tabs->filter(static fn (PersonalNotesTab $tab) => $tab->getId() === $id)->first() ?: null;
    }

    public function addTab(PersonalNotesTab $tab): self
    {
        $tab->setPersonalNotes($this);

        if (!$this->tabs->contains($tab)) {
            $this->tabs->add($tab);
        }

        return $this;
    }

    public function removeTab(PersonalNotesTab $tab): self
    {
        $this->tabs->removeElement($tab);

        return $this;
    }
}
