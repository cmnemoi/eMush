import {Action, ActionParameters} from './action';
import {Player} from '../models/player.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import {LogEnum} from '../enums/log.enum';
import ItemRepository from '../repository/item.repository';
import {Item} from '../models/item.model';
import {ItemTypeEnum} from '../enums/itemType.enum';
import {StatusEnum} from '../enums/status.enum';
import GameConfig from '../../config/game.config';
import ItemsConfig, {FoodEffects} from '../../config/item.config';
import {ActionResult} from '../enums/actionResult.enum';
import PlayerRepository from '../repository/player.repository';
import RandomService from '../services/random.service';
import {logger} from '../config/logger';
import RoomLogService from '../services/roomLog.service';
import eventManager from "../config/event.manager";
import {PlayerEvent} from "../events/player.event";

export class EatAction extends Action {
    public player!: Player;
    public food!: Item;

    constructor(player: Player) {
        super();
        this.player = player;
    }

    async loadParams(params: ActionParameters): Promise<boolean> {
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
            (this.player.room.items.some(item => item.id === this.food.id) ||
                this.player.items.some(item => item.id === this.food.id)) &&
            !this.player.statuses.includes(StatusEnum.FULL_STOMACH)
        );
    }

    createLog(): void {
        // 0FIXME how do we handle secret discovered visibility
        RoomLogService.createLog(
            LogEnum.EAT,
            {character: this.player.character},
            this.player.room,
            this.player,
            VisibilityEnum.SECRET
        );
    }

    async apply(): Promise<string> {
        const itemConfig = ItemsConfig.find(
            item => item.name === this.food.name
        );

        if (
            typeof itemConfig === 'undefined' ||
            typeof itemConfig.effects === 'undefined'
        ) {
            logger.error(
                this.food.name +
                ' does not exist or is not configurated, item id: ' +
                this.food.id
            );
            throw new Error(
                this.food.name +
                ' does not exist or is not configurated, item id: ' +
                this.food.id
            );
        }
        this.player.actionPoint = Math.max(
            Math.min(
                this.player.actionPoint + itemConfig.effects.actionPoint,
                GameConfig.maxActionPoint
            ),
            0
        );
        this.player.movementPoint = Math.max(
            Math.min(
                this.player.movementPoint + itemConfig.effects.movementPoint,
                GameConfig.maxMovementPoint
            ),
            0
        );
        this.player.healthPoint = Math.max(
            Math.min(
                this.player.healthPoint + itemConfig.effects.healthPoint,
                GameConfig.maxHealthPoint
            ),
            0
        );
        this.player.moralPoint = Math.max(
            Math.min(
                this.player.moralPoint + itemConfig.effects.moralPoint,
                GameConfig.maxMoralPoint
            ),
            0
        );
        this.player.satiety = itemConfig.effects.satiety;
        if (this.player.satiety >= 3) {
            this.player.statuses.push(StatusEnum.FULL_STOMACH);
        }
        // Remove starving status anyway
        this.player.removeStatus(StatusEnum.STARVING);

        if (RandomService.random(100) < 50) { // 50% chance of soil
            eventManager.emit(PlayerEvent.PLAYER_SOILED, this.player)
        }

        ItemRepository.remove(this.food);
        PlayerRepository.save(this.player);
        return ActionResult.SUCCESS;
    }
}
