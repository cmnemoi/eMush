<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Dismountable;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Disassemble extends Action
{
    protected const NAME = ActionEnum::DISASSEMBLE;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private SuccessRateServiceInterface $successRateService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->item = $item;

        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);
        if ($dismountableType !== null) {
            $this->actionCost->setActionPointCost($dismountableType->getActionCost());
        }
    }

    public function canExecute(): bool
    {
        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);
        //Check that the item is reachable
        return null !== $dismountableType &&
            $this->player->canReachItem($this->item) &&
            in_array(SkillEnum::TECHNICIAN, $this->player->getSkills())
        ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Dismountable $dismountableType */
        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);

        $attempt = $this->player
            ->getStatuses()
            ->filter(fn (Status $status) => StatusEnum::ATTEMPT === $status->getName())
            ->first()
        ;
        $modificator = 0; //@TODO

        $numberAttempt = 0;
        if ($attempt) {
            $numberAttempt = $attempt->getCharge();
        }

        $successChance = $this->successRateService->getSuccessRate(
            $dismountableType->getChancesSuccess(),
            $numberAttempt,
            $modificator
        );

        $random = $this->randomService->randomPercent();

        if ($random <= $successChance) {
            $this->disasemble($dismountableType);
            $this->player->removeStatus($attempt);
            $response = new Success();
        } else {
            $response = new Fail();
            if (!$attempt) {
                $attempt = $this->statusService->createAttemptStatus(
                    StatusEnum::ATTEMPT,
                    self::NAME,
                    $this->player
                );
            }
            $attempt->addCharge(1);
            $this->player->addStatus($attempt);
        }

        $this->playerService->persist($this->player);

        return new $response();
    }

    private function disasemble(Dismountable $dismountableType)
    {
        // add the item produced by disassembling
        foreach ($dismountableType->getProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $productItem = $this
                    ->gameItemService
                    ->createGameItemFromName($productString, $this->player->getDaedalus())
                ;
                if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
                    $productItem->setPlayer($this->player);
                } else {
                    $productItem->setRoom($this->player->getRoom());
                }
                $this->gameItemService->persist($productItem);
            }
        }

        // remove the dismanteled item
        $this->item
            ->setRoom(null)
            ->setPlayer(null)
        ;

        $this->gameItemService->delete($this->item);
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::DISASSEMBLE,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
