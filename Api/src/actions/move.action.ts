import {Action} from './action';
import {Player} from '../models/player.model';
import {RoomLog} from '../models/roomLog.model';
import PlayerService from '../services/player.service';
import {ActionResult} from '../enums/actionResult.enum';
import {Door} from '../models/door.model';
import {Room} from '../models/room.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import {LogEnum} from '../enums/log.enum';
import RoomLogRepository from '../repository/roomLog.repository';
import DoorRepository from '../repository/door.repository';
import RoomLogService from "../services/roomLog.service";
import {logger} from "../config/logger";

export class MoveAction extends Action {
    public player!: Player;
    public door!: Door;

    constructor(player: Player) {
        super();
        this.player = player;
    }

    async loadParams(params: any): Promise<boolean> {
        if (typeof params.door === 'undefined') {
            return false;
        }
        const door = await DoorRepository.find(Number(params.door), {
            relations: ['rooms'],
        });
        if (door === null) {
            return false;
        }
        this.door = door;

        return true;
    }

    canExecute(): boolean {
        return (
            this.player.room.doors.some(door => door.id === this.door.id) &&
            !this.door.isBroken() &&
            (this.player.movementPoint > 0 || this.player.actionPoint > 0)
        );
    }

    createLog(): void {

        const oldRoom = this.door.rooms.find(
            (room: Room) => room.id !== this.player.room.id
        );

        if (typeof oldRoom === 'undefined') {
            logger.error("Could not find previous room")
            return;
        }

        RoomLogService.createLog(LogEnum.EXIT_ROOM, {character: this.player.character}, oldRoom, this.player, VisibilityEnum.PUBLIC)
        RoomLogService.createLog(LogEnum.ENTER_ROOM, {character: this.player.character}, this.player.room, this.player, VisibilityEnum.PUBLIC)
    }

    async apply(): Promise<string> {
        if (this.player.movementPoint > 0) {
            this.player.movementPoint--;
        } else if (this.player.actionPoint > 0) {
            this.player.actionPoint--;
            this.player.movementPoint = 2;
        }

        const newRoom = this.door.rooms.find(
            (room: Room) => room.id !== this.player.room.id
        );

        if (typeof newRoom !== 'undefined') {
            this.player.room = newRoom;
        }

        await PlayerService.save(this.player);
        return ActionResult.SUCCESS;
    }
}
