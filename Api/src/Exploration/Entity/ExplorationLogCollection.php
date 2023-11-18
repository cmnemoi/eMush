<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<int, ExplorationLog>
 */
final class ExplorationLogCollection extends ArrayCollection
{
}
