import {Action, ActionParameters} from './action';
import {Player} from '../models/player.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import {LogEnum} from '../enums/log.enum';
import ItemRepository from '../repository/item.repository';
import {Item} from '../models/item.model';
import {StatusEnum} from '../enums/status.enum';
import GameConfig from '../../config/game.config';
import {ActionResult} from '../enums/actionResult.enum';
import PlayerRepository from '../repository/player.repository';
import RoomLogService from '../services/roomLog.service';
import {SkillsEnum} from '../enums/skills.enum';

export class TakeAction extends Action {
    public player!: Player;
    public item!: Item;

    constructor(player: Player) {
        super();
        this.player = player;
    }

    async loadParams(params: ActionParameters): Promise<boolean> {
        if (typeof params.item === 'undefined') {
            return false;
        }
        const item = await ItemRepository.find(Number(params.item));
        if (item === null) {
            return false;
        }
        this.item = item;

        return true;
    }

    canExecute(): boolean {
        return (
            this.player.room.hasItem(this.item) &&
            this.player.items.length < GameConfig.maxItemInInventory &&
            this.item.isMoveable
        );
    }

    createLog(): void {
        RoomLogService.createLog(
            LogEnum.TAKE,
            {character: this.player.character, item: this.item.name},
            this.player.room,
            this.player,
            this.item.isPersonal()
                ? VisibilityEnum.PRIVATE
                : VisibilityEnum.PUBLIC
        );
    }

    async apply(): Promise<string> {
        this.player.addItem(this.item);
        this.player.room.removeItem(this.item);

        // add BURDENED status if item is heavy and player hasn't SOLID skill
        if (
            this.item.isHeavy &&
            !this.player.skills.includes(SkillsEnum.SOLID)
        ) {
            this.player.statuses.push(StatusEnum.BURDENED);
        }

        await ItemRepository.save(this.item);
        await PlayerRepository.save(this.player);
        return ActionResult.SUCCESS;
    }
}
