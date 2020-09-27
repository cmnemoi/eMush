import {Player} from '../models/player.model';
import {Room} from '../models/room.model';
import {Daedalus} from '../models/daedalus.model';
import {RoomLog} from '../models/roomLog.model';
import {logger} from './logger';

export default async (forceInit = false) => {
    if (forceInit) {
        RoomLog.drop().then(() => logger.info('RoomLog table dropped'));
        Player.drop().then(() => logger.info('Player table dropped'));
        Room.drop().then(() => logger.info('Room table dropped'));
        Daedalus.drop().then(() => logger.info('Daedalus table dropped'));
    }

    Daedalus.hasMany(Player, {
        foreignKey: 'daedalus_id',
        as: 'players',
    });

    Daedalus.hasMany(Room, {
        foreignKey: 'daedalus_id',
        as: 'rooms',
    });

    Room.belongsTo(Daedalus, {
        foreignKey: 'daedalus_id',
        as: 'daedalus',
    });

    Room.hasMany(Player, {
        foreignKey: 'room_id',
        as: 'players',
    });

    Player.belongsTo(Daedalus, {
        foreignKey: 'daedalus_id',
        as: 'daedalus',
    });

    Player.belongsTo(Room, {
        foreignKey: 'room_id',
        as: 'room',
    });

    await RoomLog.sync({force: forceInit}).then(() =>
        logger.info('Daedalus table created')
    );
    await Daedalus.sync({force: forceInit}).then(() =>
        logger.info('Daedalus table created')
    );
    await Room.sync({force: forceInit}).then(() =>
        logger.info('Room table created')
    );
    await Player.sync({force: forceInit})
        .then(() => logger.info('Player table created'))
        .catch(err => logger.error(err));
};
