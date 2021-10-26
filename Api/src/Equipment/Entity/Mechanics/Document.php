<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class Equipment.
 *
 * @ORM\Entity
 */
class Document extends Tool
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $content;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isTranslated = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $canShred = false;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mechanics[] = EquipmentMechanicEnum::DOCUMENT;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return static
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isTranslated(): bool
    {
        return $this->isTranslated;
    }

    /**
     * @return static
     */
    public function setIsTranslated(bool $isTranslated): self
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }

    public function canShred(): bool
    {
        return $this->canShred;
    }

    /**
     * @return static
     */
    public function setCanShred(bool $canShred): self
    {
        $this->canShred = $canShred;

        return $this;
    }
}
