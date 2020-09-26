import {Daedalus} from '../models/daedalus.model';
import {Identifier} from 'sequelize';
import DaedalusConfig from '../config/daedalus.config';
import {Room} from '../models/room.model';
import {Player} from '../models/player.model';

export default class DaedalusService {
    public static findAll(): Promise<Daedalus[]> {
        return Daedalus.findAll<Daedalus>({});
    }

    public static find(id: Identifier): Promise<Daedalus | null> {
        return Daedalus.findByPk<Daedalus>(id, {
            include: [
                {
                    model: Room,
                    as: 'rooms',
                },
                {
                    model: Player,
                    as: 'players',
                },
            ],
        });
    }

    public static save(daedalus: Daedalus): Promise<Daedalus> {
        return daedalus.save();
    }

    public static async initDaedalus(): Promise<Daedalus> {
        const daedalus = Daedalus.build(
            {},
            {
                include: [{model: Room, as: 'rooms'}],
            }
        );
        daedalus.cycle = 0; // @TODO
        daedalus.day = 0; // @TODO
        daedalus.oxygen = DaedalusConfig.initOxygen;
        daedalus.fuel = DaedalusConfig.initFuel;
        daedalus.hull = DaedalusConfig.initHull;
        daedalus.shield = DaedalusConfig.initShield;

        const rooms: Room[] = [];

        await Promise.all(
            DaedalusConfig.rooms.map(async roomConfig => {
                const room = Room.build();
                room.name = roomConfig.name;
                rooms.push(room);
            })
        );

        daedalus.rooms = rooms;

        return daedalus.save();
    }
}
