import {Action, ActionParameters} from './action';
import {Player} from '../models/player.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import {LogEnum} from '../enums/log.enum';
import ItemRepository from '../repository/item.repository';
import {Item} from '../models/item.model';
import {StatusEnum} from '../enums/status.enum';
import {ActionResult} from '../enums/actionResult.enum';
import PlayerRepository from '../repository/player.repository';
import RoomLogService from '../services/roomLog.service';

export class DropAction extends Action {
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
        return this.player.hasItem(this.item);
    }

    createLog(): void {
        RoomLogService.createLog(
            LogEnum.DROP,
            {character: this.player.character, item: this.item.name},
            this.player.room,
            this.player,
            this.item.isPersonal()
                ? VisibilityEnum.PRIVATE
                : VisibilityEnum.PUBLIC
        );
    }

    async apply(): Promise<string> {
        this.player.room.addItem(this.item);
        this.player.removeItem(this.item);

        // Remove BURDENED status if no other heavy item in the inventory
        if (
            this.item.isHeavy &&
            this.player.hasStatus(StatusEnum.BURDENED) &&
            this.player.items.some((item: Item) => item.isHeavy)
        ) {
            this.player.removeStatus(StatusEnum.BURDENED);
        }

        await ItemRepository.save(this.item);
        await PlayerRepository.save(this.player);
        return ActionResult.SUCCESS;
    }
}
