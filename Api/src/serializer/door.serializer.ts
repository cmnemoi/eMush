import {User} from '../models/user.model';
import {Door} from '../models/door.model';
import {ActionsEnum} from '../enums/actions.enum';

export function doorSerializer(
    door: Door,
    user: User
): Record<string, unknown> {
    return {
        id: door.id,
        name: door.name,
        statuses: door.statuses,
        actions: [ActionsEnum.MOVE],
    };
}
