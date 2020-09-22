import { Sequelize } from "sequelize";

// @ts-ignore
export const database = new Sequelize({
    dialect: "mariadb",
    username: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    database: process.env.DB_NAME
});