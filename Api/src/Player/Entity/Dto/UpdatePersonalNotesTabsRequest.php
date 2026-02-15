<?php

namespace Mush\Player\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePersonalNotesTabsRequest
{
    /**
     * @Assert\NotNull(message="Tabs array is required")
     *
     * @Assert\Type("array")
     *
     * @Assert\Count(max=16, maxMessage="Maximum number of tabs (16) exceeded")
     *
     * @Assert\All({
     *
     *     @Assert\Collection(
     *         fields={
     *             "id"=@Assert\Optional({
     *
     *                 @Assert\Type("integer")
     *             }),
     *             "index"=@Assert\Required({
     *
     *                 @Assert\NotNull(),
     *
     *                 @Assert\Type("integer"),
     *
     *                 @Assert\PositiveOrZero(message="Order must be a positive integer")
     *             }),
     *             "icon"=@Assert\Required({
     *
     *                 @Assert\Type("string")
     *             }),
     *             "content"=@Assert\Required({
     *                 @Assert\Type("string"),
     *
     *                 @Assert\Length(max=65536, maxMessage="Maximum length of content ({{limit}}) exceeded")
     *             })
     *         },
     *         allowExtraFields=true
     *     )
     * })
     */
    private array $tabs = [];

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function setTabs(array $tabs): self
    {
        $this->tabs = $tabs;

        return $this;
    }
}
