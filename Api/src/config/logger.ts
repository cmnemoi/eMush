import {createLogger, format, transports} from 'winston';

const loggerConfig = createLogger({
    format: format.combine(
        format.timestamp({format: 'YYYY-MM-DD HH:mm:ss:ms'}),
        format.printf(
            info => `${info.timestamp} ${info.level}: ${info.message}`
        )
    ),
    transports: [
        new transports.File({
            filename: './logs/all-logs.log',
            maxsize: 5242880,
            maxFiles: 5,
        }),
    ],
    exceptionHandlers: [
        new transports.File({filename: './logs/exceptions.log'}),
    ],
});

if (process.env.NODE_ENV !== 'production') {
    loggerConfig.add(
        new transports.Console({
            format: format.simple(),
        })
    );
}

export const logger = loggerConfig;

import {Logger, QueryRunner} from 'typeorm';

/* eslint-disable  @typescript-eslint/no-explicit-any */
export class TypeOrmLogger implements Logger {
    log(level: 'log' | 'info' | 'warn', message: string): any {
        logger.log({level, message});
    }

    logMigration(message: string): any {
        logger.info(message);
    }

    logQuery(query: string, parameters?: any[]): any {
        logger.info(query);
        logger.info(parameters);
    }

    logQueryError(error: string, query: string): any {
        logger.error(error, query);
    }

    logQuerySlow(time: number, query: string, parameters?: any[]): any {
        logger.warning(query, time.toString(), parameters);
    }

    logSchemaBuild(message: string): any {
        logger.info(message);
    }
}
