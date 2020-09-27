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
    loggerConfig.add(new transports.Console({
        format: format.simple(),
    }));
}

export const logger = loggerConfig;
