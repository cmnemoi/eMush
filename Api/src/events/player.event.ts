import {Player} from '../models/player.model';
import RoomLogService from '../services/roomLog.service';
import {RoomLog} from '../models/roomLog.model';
import eventManager from '../config/event.manager';
import DaedalusConfig from '../config/daedalus.config';
import {DaedalusEvent} from './daedalus.event';

export enum PlayerEvent {
    PLAYER_AWAKEN = 'player_awaken',
    PLAYER_DIE = 'player_die',
}

const playerAwaken = (player: Player) => {
    const roomLog = RoomLog.build();
    roomLog.roomId = player.room.id;
    roomLog.createdAt = new Date();
    roomLog.log = 'player awaken';
    RoomLogService.save(roomLog);

    if (player.daedalus.players.length === DaedalusConfig.maxPlayer) {
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

eventManager.on(PlayerEvent.PLAYER_AWAKEN, playerAwaken);
eventManager.on(PlayerEvent.PLAYER_DIE, playerDie);
