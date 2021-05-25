<?php


namespace Mush\Tests\functional\Disease\Listener;


use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Listener\ActionSubscriber;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Player\Entity\Player;

class ActionSubscriberCest
{
    private ActionSubscriber $actionSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->actionSubscriber = $I->grabService(ActionSubscriber::class);
    }

    public function testOnPostActionConsume(FunctionalTester $I)
    {
        $action = new Action();
        $action->setName(ActionEnum::CONSUME);

        $daedalus = $I->have(Daedalus::class);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus
        ]);

        $event = new ActionEvent($action, $player);

        $this->actionSubscriber->onPostAction($event);
    }
}