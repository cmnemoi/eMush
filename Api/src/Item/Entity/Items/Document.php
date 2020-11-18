<?php

namespace Mush\Item\Entity\Items;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class Item.
 *
 * @ORM\Entity
 */
class Document extends Tool
{
    protected string $type = ItemTypeEnum::DOCUMENT;

     /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $content;

     /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isTranslated = false;


    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Blueprint
    {
        $this->content = $content;

        return $this;
    }

    public function getIsTranslated(): bool
    {
        return $this->isTranslated;
    }

    public function setIsTranslated(bool $isTranslated): Blueprint
    {
        $this->isTranslated = $isTranslated;

        return $this;
    }
}
