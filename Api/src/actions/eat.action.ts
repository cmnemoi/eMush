import {Action} from './action';
import {Player} from '../models/player.model';
import {RoomLog} from '../models/roomLog.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import {EatLogEnum} from '../enums/log.enum';
import RoomLogRepository from '../repository/roomLog.repository';
import ItemRepository from "../repository/item.repository";
import {Item} from "../models/item.model";
import {ItemTypeEnum} from "../enums/itemType.enum";
import {StatusEnum} from "../enums/status.enum";
import GameConfig from "../../config/game.config"
import {ActionResult} from "../enums/actionResult.enum";
import PlayerRepository from "../repository/player.repository";
import RandomService from "../services/random.service";

export class EatAction extends Action {
    public player!: Player;
    public food!: Item;

    constructor(player: Player) {
        super();
        this.player = player;
    }

    async loadParams(params: any): Promise<boolean> {
        if (typeof params.item === 'undefined') {
            return false;
        }
        const food = await ItemRepository.find(Number(params.item));
        if (food === null) {
            return false;
        }
        this.food = food;

        if (food.type !== ItemTypeEnum.RATION) {
            return false;
        }

        return true;
    }

    canExecute(): boolean {
        return (
            (this.player.room.items.some(item => item.id === this.food.id) || this.player.items.some(item => item.id === this.food.id)) &&
            !this.player.statuses.includes(StatusEnum.FULL_STOMACH)
        );
    }

    createLog(): void {
        const eqtLog = new RoomLog();
        eqtLog.roomId = this.player.room.id;
        eqtLog.playerId = this.player.id;
        eqtLog.visibility = VisibilityEnum.SECRET;
        eqtLog.message = EatLogEnum.EAT_1;

        RoomLogRepository.save(eqtLog);
    }

    async apply(): Promise<string> {
        switch (this.food.name) {
            case ItemTypeEnum.RATION: // TODO checkhow to make this value configurable
                this.player.actionPoint = Math.min(this.player.actionPoint + 4, GameConfig.maxActionPoint);
                this.player.movementPoint = Math.max(this.player.actionPoint - 1, 0);
                this.player.satiety = 4;
                this.player.statuses.push(StatusEnum.FULL_STOMACH);
                break;
        }

        if (RandomService.random() < 20) { // @TODO: how do we handle dirty ? (event?)
            this.player.setDirty();
        }

        ItemRepository.remove(this.food);
        PlayerRepository.save(this.player);
        return ActionResult.SUCCESS;
    }
}
