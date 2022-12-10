<?php

namespace Mush\RoomLog\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\RoomLog\Entity\RoomLog;

/**
 * @template-extends ArrayCollection<int, RoomLog>
 */
class RoomLogCollection extends ArrayCollection
{
}
