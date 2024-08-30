<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;
use Mush\MetaGame\Entity\UnlockCondition;

#[ORM\Entity]
#[ORM\Table(name: 'skin')]
class Skin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name = 'default';

    #[ORM\ManyToOne(targetEntity: UnlockCondition::class)]
    private UnlockCondition $unlockCondition;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUnlockCondition(): UnlockCondition
    {
        return $this->unlockCondition;
    }

    public function setUnlockCondition(UnlockCondition $unlockCondition): self
    {
        $this->unlockCondition = $unlockCondition;

        return $this;
    }
}
