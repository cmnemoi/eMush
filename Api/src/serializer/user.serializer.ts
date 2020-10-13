import {User} from '../models/user.model';
import PlayerService from '../services/player.service';

export async function userSerializer(
    user: User,
    authenticatedUSer: User
): Promise<Record<string, unknown>> {
    const currentGame = await PlayerService.findCurrentPlayer(user);
    return {
        id: user.id,
        username: user.username,
        currentGame: currentGame?.id,
    };
}
