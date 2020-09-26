import {Action} from './action';
import {Player} from '../models/player.model';
import eventManager from '../config/event.manager';
import {PlayerEvent} from '../events/player.event';
import {RoomLog} from '../models/roomLog.model';

export class HitAction implements Action {
    public emitter!: Player;
    public receiver!: Player;

    canExecute(): boolean {
        return this.emitter.room === this.receiver.room;
    }

    //@ TODO really calculate the action
    execute(): void {
        this.receiver.healthPoint--;
        this.receiver.save();

        this.createLog();

        if (this.receiver.healthPoint === 0) {
            eventManager.emit(PlayerEvent.PLAYER_DIE, this.receiver);
        }
    }

    createLog(): Promise<RoomLog> {
        return RoomLog.create({
            roomId: this.emitter.room.id,
            createdAt: new Date(),
            log: 'player hitted another player', // 0TODO
        });
    }
}
