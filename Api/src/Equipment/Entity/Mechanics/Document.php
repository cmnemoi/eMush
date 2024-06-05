<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

#[ORM\Entity]
class Document extends Tool
{
    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $content = '';

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isTranslated = false;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $canShred = false;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DOCUMENT;

        return $mechanics;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isTranslated(): bool
    {
        return $this->isTranslated;
    }

    public function setIsTranslated(bool $isTranslated): static
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }

    public function canShred(): bool
    {
        return $this->canShred;
    }

    public function setCanShred(bool $canShred): static
    {
        $this->canShred = $canShred;

        return $this;
    }
}
