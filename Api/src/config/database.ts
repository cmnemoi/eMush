import {Options, Sequelize} from 'sequelize';
import {logger} from './logger';

const config: Options = {
    dialect: 'mariadb' as 'mariadb',
    username: process.env.DB_USER ?? '',
    password: process.env.DB_PASSWORD ?? '',
    host: process.env.DB_HOST,
    port: Number(process.env.DB_PORT),
    database: process.env.DB_NAME ?? '',
    logging: sql => logger.info(sql),
};

export const database = new Sequelize(config);
