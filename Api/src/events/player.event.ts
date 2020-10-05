import {Player} from '../models/player.model';
import eventManager from '../config/event.manager';
import GameConfig from '../../config/game.config';
import {DaedalusEvent} from './daedalus.event';
import PlayerService from '../services/player.service';
import {StatusEnum} from '../enums/status.enum';
import RoomLogService from "../services/roomLog.service";
import {LogEnum} from "../enums/log.enum";
import {VisibilityEnum} from "../enums/visibility.enum";

export enum PlayerEvent {
    PLAYER_AWAKEN = 'player_awaken',
    PLAYER_DIE = 'player_die',
    PLAYER_NEW_CYCLE = 'player_new_cycle',
    PLAYER_NEW_DAY = 'player_new_day',
}

const playerAwaken = (player: Player) => {
    RoomLogService.createLog(LogEnum.AWAKEN, {character: player.character}, player.room, player, VisibilityEnum.PRIVATE)
    if (player.daedalus.players.length === GameConfig.maxPlayer) {
        eventManager.emit(DaedalusEvent.DAEDALUS_START, player.daedalus);
    }
};

const playerDie = (player: Player) => {
    if (player.daedalus.getPlayersAlive().length === 0) {
        eventManager.emit(DaedalusEvent.DAEDALUS_END, player.daedalus);
    }
};

// @TODO: handle logs time to match the cycle change instead of the current date
const playerNewCycle = (player: Player, date: Date) => {
    player.satiety--;
    player.actionPoint++;
    RoomLogService.createLog(LogEnum.GAIN_ACTION_POINT, {number: 1}, player.room, player, VisibilityEnum.PRIVATE, date)
    player.movementPoint++;
    RoomLogService.createLog(LogEnum.GAIN_MOVEMENT_POINT, {number: 1}, player.room, player, VisibilityEnum.PRIVATE, date)

    for (const status of player.statuses) {
        switch (status) {
            case StatusEnum.STARVING: {
                player.healthPoint--;
                break;
            }
            case StatusEnum.FULL_STOMACH: {
                if (player.satiety < 3) {
                    const indexFullStomach = player.statuses.indexOf(
                        StatusEnum.FULL_STOMACH
                    );
                    if (indexFullStomach > -1) {
                        player.statuses.splice(indexFullStomach, 1);
                    }
                }
                if (player.satiety <= -24 && !player.isStarving()) {
                    player.statuses.push(StatusEnum.STARVING);
                }
            }
        }
    }

    PlayerService.save(player);
};

const playerNewDay = (player: Player, date: Date) => {
    player.moralPoint--;
    RoomLogService.createLog(LogEnum.GAIN_ACTION_POINT, {number: 1}, player.room, player, VisibilityEnum.PRIVATE, date)
    RoomLogService.createLog(LogEnum.NEW_DAY, {}, player.room, player, VisibilityEnum.PRIVATE, date)
    player.healthPoint++;
    RoomLogService.createLog(LogEnum.GAIN_ACTION_POINT, {number: 1}, player.room, player, VisibilityEnum.PRIVATE, date)

    PlayerService.save(player);
};

eventManager.on(PlayerEvent.PLAYER_AWAKEN, playerAwaken);
eventManager.on(PlayerEvent.PLAYER_DIE, playerDie);
eventManager.on(PlayerEvent.PLAYER_NEW_CYCLE, playerNewCycle);
eventManager.on(PlayerEvent.PLAYER_NEW_DAY, playerNewDay);
