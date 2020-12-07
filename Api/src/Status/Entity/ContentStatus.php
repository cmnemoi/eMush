<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ContentStatus.
 *
 * @ORM\Entity()
 */
class ContentStatus extends Status
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): ContentStatus
    {
        $this->content = $content;

        return $this;
    }
}
