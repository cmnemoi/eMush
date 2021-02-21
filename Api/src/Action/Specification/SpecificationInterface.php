<?php


namespace Mush\Action\Specification;


use Mush\Action\Entity\ActionParameter;
use Mush\Player\Entity\Player;

interface SpecificationInterface
{
    public function isValid(ActionParameter $parameter, Player $player, ?array $parameters): bool;
}