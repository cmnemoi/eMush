import {RoomLog} from '../models/roomLog.model';

export interface Action {
    canExecute(): boolean;
    execute(): void;
    createLog(): Promise<RoomLog>;
}
