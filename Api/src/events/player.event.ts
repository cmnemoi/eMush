import {Player} from '../models/player.model';
import RoomLogService from '../services/roomLog.service';
import {RoomLog} from '../models/roomLog.model';
import eventManager from '../config/event.manager';
import GameConfig from '../../config/game.config';
import {DaedalusEvent} from './daedalus.event';

export enum PlayerEvent {
    PLAYER_AWAKEN = 'player_awaken',
    PLAYER_DIE = 'player_die',
    PLAYER_NEW_CYCLE = 'player_new_cycle',
    PLAYER_NEW_DAY = 'player_new_day',
}

const playerAwaken = (player: Player) => {
    const roomLog = RoomLog.build();
    roomLog.roomId = player.room.id;
    roomLog.createdAt = new Date();
    roomLog.log = 'player awaken';
    RoomLogService.save(roomLog);

    if (player.daedalus.players.length === GameConfig.maxPlayer) {
        eventManager.emit(DaedalusEvent.DAEDALUS_START, player.daedalus);
    }
};

const playerDie = (player: Player) => {
    const roomLog = RoomLog.build();
    roomLog.roomId = player.room.id;
    roomLog.createdAt = new Date();
    roomLog.log = 'player die';
    RoomLogService.save(roomLog);

    if (player.daedalus.getPlayersAlive().length === 0) {
        eventManager.emit(DaedalusEvent.DAEDALUS_END, player.daedalus);
    }
};

const playerNewCycle = (player: Player) => {
    player.moralPoint--;
    player.satiety--;

    player.save();
};

const playerNewDay = (player: Player) => {
    player.healthPoint++;

    player.save();
};

eventManager.on(PlayerEvent.PLAYER_AWAKEN, playerAwaken);
eventManager.on(PlayerEvent.PLAYER_DIE, playerDie);
eventManager.on(PlayerEvent.PLAYER_NEW_CYCLE, playerNewCycle);
eventManager.on(PlayerEvent.PLAYER_NEW_DAY, playerNewDay);
