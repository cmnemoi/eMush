import {User} from '../models/user.model';
import {Daedalus} from '../models/daedalus.model';
import {Player} from '../models/player.model';
import GameConfig from '../../config/game.config';

export function daedalusSerializer(
    daedalus: Daedalus,
    user: User | null
): Record<string, unknown> {
    return {
        id: daedalus.id,
        numberMaxPlayer: GameConfig.maxPlayer,
        numberHumanAlive: daedalus.players.filter(
            (player: Player) => !player.isDead() && !player.isMush()
        ).length,
        numberHumanDead: daedalus.players.filter(
            (player: Player) => player.isDead() && !player.isMush()
        ).length,
        numberMushAlive: daedalus.players.filter(
            (player: Player) => !player.isDead() && player.isMush()
        ).length,
        numberMushDead: daedalus.players.filter(
            (player: Player) => player.isDead() && player.isMush()
        ).length,
        oxygen: daedalus.oxygen,
        fuel: daedalus.fuel,
        day: daedalus.day,
        cycle: daedalus.cycle,
        shield: daedalus.shield,
        createdAt: daedalus.createdAt,
        updatedAt: daedalus.updatedAt,
    };
}
