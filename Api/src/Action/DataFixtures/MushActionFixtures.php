<?php

declare(strict_types=1);

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;

class MushActionFixtures extends Fixture
{
    public const SPREAD_FIRE = 'spread.fire';
    public const EXTRACT_SPORE = 'extract.spore';
    public const INFECT_PLAYER = 'infect.player';
    public const MAKE_SICK = 'make.sick';
    public const FAKE_DISEASE = 'fake.disease';
    public const SCREW_TALKIE = 'screw.talkie';
    public const CONVERT_CAT = 'convert.cat';
    public const GO_BERSERK = 'go_berserk';

    public function load(ObjectManager $manager): void
    {
        $extractSporeAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::EXTRACT_SPORE)
        );
        $manager->persist($extractSporeAction);

        $infectAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::INFECT)
        );
        $manager->persist($infectAction);

        $spreadFireAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::SPREAD_FIRE)
        );
        $manager->persist($spreadFireAction);

        $makeSickAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::MAKE_SICK)
        );
        $manager->persist($makeSickAction);

        $fakeDiseaseAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::FAKE_DISEASE)
        );
        $manager->persist($fakeDiseaseAction);

        $screwTalkieAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::SCREW_TALKIE)
        );
        $manager->persist($screwTalkieAction);

        $phagocyteAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::PHAGOCYTE)
        );
        $manager->persist($phagocyteAction);

        $trapClosetAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::TRAP_CLOSET)
        );
        $manager->persist($trapClosetAction);

        $exchangeBoodyAction = ActionConfig::fromConfigData(
            ActionData::getByName(ActionEnum::EXCHANGE_BODY)
        );
        $manager->persist($exchangeBoodyAction);

        $convertCatAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONVERT_CAT));
        $manager->persist($convertCatAction);

        $goBerserkAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GO_BERSERK));
        $manager->persist($goBerserkAction);

        $removeCameraNimbleFingersAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REMOVE_CAMERA_NIMBLE_FINGERS));
        $manager->persist($removeCameraNimbleFingersAction);

        $installCameraNimbleFingersAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSTALL_CAMERA_NIMBLE_FINGERS));
        $manager->persist($installCameraNimbleFingersAction);

        $bypassTerminalAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BYPASS_TERMINAL));
        $manager->persist($bypassTerminalAction);

        $manager->flush();

        $this->addReference(self::EXTRACT_SPORE, $extractSporeAction);
        $this->addReference(self::INFECT_PLAYER, $infectAction);
        $this->addReference(ActionEnum::SPREAD_FIRE->value, $spreadFireAction);
        $this->addReference(ActionEnum::MAKE_SICK->value, $makeSickAction);
        $this->addReference(self::FAKE_DISEASE, $fakeDiseaseAction);
        $this->addReference(ActionEnum::SCREW_TALKIE->value, $screwTalkieAction);
        $this->addReference(ActionEnum::PHAGOCYTE->value, $phagocyteAction);
        $this->addReference(ActionEnum::TRAP_CLOSET->value, $trapClosetAction);
        $this->addReference(ActionEnum::EXCHANGE_BODY->value, $exchangeBoodyAction);
        $this->addReference(ActionEnum::CONVERT_CAT->value, $convertCatAction);
        $this->addReference(ActionEnum::GO_BERSERK->value, $goBerserkAction);
        $this->addReference(ActionEnum::REMOVE_CAMERA_NIMBLE_FINGERS->value, $removeCameraNimbleFingersAction);
        $this->addReference(ActionEnum::INSTALL_CAMERA_NIMBLE_FINGERS->value, $installCameraNimbleFingersAction);
        $this->addReference(ActionEnum::BYPASS_TERMINAL->value, $bypassTerminalAction);
    }
}
