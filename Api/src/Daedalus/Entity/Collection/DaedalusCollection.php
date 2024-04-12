<?php

namespace Mush\Daedalus\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @template-extends ArrayCollection<int, Daedalus>
 */
class DaedalusCollection extends ArrayCollection {}
