import 'reflect-metadata';
import {createConnection} from 'typeorm';
import {Daedalus} from '../models/daedalus.model';
import {Room} from '../models/room.model';
import {RoomLog} from '../models/roomLog.model';
import {Player} from '../models/player.model';
import {ConnectionOptions} from 'typeorm/connection/ConnectionOptions';
import {Door} from "../models/door.model";
import {TypeOrmLogger} from "./logger";

const dbConfig: ConnectionOptions = {
    type: 'mysql' || '',
    host: process.env.DB_HOST || '',
    port: Number(process.env.DB_PORT),
    username: process.env.DB_USER || '',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || '',
    entities: [Daedalus, Room, RoomLog, Player, Door],
    synchronize: process.env.NODE_ENV === 'test',
    logging: false,
    migrationsTableName: 'migrations',
    migrations: [process.cwd() + '/build/src/migration/*.js'],
    cli: {
        migrationsDir: 'migration',
    },
};

export default createConnection(dbConfig);
