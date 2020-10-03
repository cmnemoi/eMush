import {Player} from '../models/player.model';
import RoomLogService from '../services/roomLog.service';
import {RoomLog} from '../models/roomLog.model';
import eventManager from '../config/event.manager';
import GameConfig from '../../config/game.config';
import {DaedalusEvent} from './daedalus.event';
import PlayerService from '../services/player.service';

export enum PlayerEvent {
    PLAYER_AWAKEN = 'player_awaken',
    PLAYER_DIE = 'player_die',
    PLAYER_NEW_CYCLE = 'player_new_cycle',
    PLAYER_NEW_DAY = 'player_new_day',
}

const playerAwaken = (player: Player) => {
    if (player.daedalus.players.length === GameConfig.maxPlayer) {
        eventManager.emit(DaedalusEvent.DAEDALUS_START, player.daedalus);
    }
};

const playerDie = (player: Player) => {
    if (player.daedalus.getPlayersAlive().length === 0) {
        eventManager.emit(DaedalusEvent.DAEDALUS_END, player.daedalus);
    }
};

const playerNewCycle = (player: Player) => {
    player.moralPoint--;
    player.satiety--;

    PlayerService.save(player);
};

const playerNewDay = (player: Player) => {
    player.healthPoint++;

    PlayerService.save(player);
};

eventManager.on(PlayerEvent.PLAYER_AWAKEN, playerAwaken);
eventManager.on(PlayerEvent.PLAYER_DIE, playerDie);
eventManager.on(PlayerEvent.PLAYER_NEW_CYCLE, playerNewCycle);
eventManager.on(PlayerEvent.PLAYER_NEW_DAY, playerNewDay);
