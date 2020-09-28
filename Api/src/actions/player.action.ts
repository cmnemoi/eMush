import {Action} from './action';
import {Player} from '../models/player.model';
import eventManager from '../config/event.manager';
import {PlayerEvent} from '../events/player.event';
import {RoomLog} from '../models/roomLog.model';
import PlayerService from '../services/player.service';
import RoomLogService from '../services/roomLog.service';

export class HitAction implements Action {
    public emitter!: Player;
    public receiver!: Player;

    canExecute(): boolean {
        return this.emitter.room === this.receiver.room;
    }

    // @TODO really calculate the action
    execute(): void {
        this.receiver.healthPoint--;
        PlayerService.save(this.receiver);

        this.createLog();

        if (this.receiver.healthPoint === 0) {
            eventManager.emit(PlayerEvent.PLAYER_DIE, this.receiver);
        }
    }

    createLog(): Promise<RoomLog> {
        const roomLog = new RoomLog();
        roomLog.roomId = this.emitter.room.id;
        roomLog.log = 'player hit another player';
        roomLog.createdAt = new Date();

        return RoomLogService.save(roomLog);
    }
}
