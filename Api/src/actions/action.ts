import {ActionResult} from '../enums/actionResult.enum';
import {CharactersEnum} from '../enums/characters.enum';
import {RoomEnum} from '../enums/room.enum';
import {ItemsEnum} from '../enums/items.enum';

export interface ActionParameters {
    room?: number | null;
    item?: number | null;
    door?: number | null;
    player?: number | null;
}

export abstract class Action {
    public abstract async loadParams(
        params: ActionParameters
    ): Promise<boolean>;
    public abstract canExecute(): boolean;
    protected abstract async apply(): Promise<string>;
    protected abstract createLog(actionResult: string): void;

    public async execute(): Promise<string> {
        if (!this.canExecute()) {
            return ActionResult.NONE;
        }

        const result = await this.apply();
        this.createLog(result);

        return result;
    }
}
