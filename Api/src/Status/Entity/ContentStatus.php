<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ContentStatus extends Status
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $content;

    /**
     * @return static
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFormattedContent(): string
    {
        return $this->content !== '' ? sprintf('« %s »', $this->content) : '« »';
    }
}
