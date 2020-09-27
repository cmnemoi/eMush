
module.exports = {
    type: "mysql" || '',
    host: process.env.DB_HOST || '',
    port: Number(process.env.DB_PORT),
    username: process.env.DB_USER || '',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || '',
    entities: ['build/**/models/*{.ts,.js}'],
    synchronize: false,
    migrationsTableName: 'migration',
    migrations: ['build/migration/*.js'],
    cli: {
        migrationsDir: 'migration',
    },
}