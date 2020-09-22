import * as dotenv from 'dotenv';
import * as fs from "fs";

// If test environment and .env.test exit; load this file
if (process.env.NODE_ENV === "test") {
    try {
        if(fs.existsSync(__dirname + "/../.env.test")) {
            dotenv.config({path: __dirname + "/../.env.test"});
        } else {
            dotenv.config();
        }
    } catch (err) {
        console.error(err);
    }
} else {
    dotenv.config();
}

import express from "express";
import * as bodyParser from "body-parser";
import { Routes } from "./config/routes";

const PORT = process.env.SERVER_PORT;

class App {
    public app: express.Application;
    public routePrv: Routes = new Routes();

    constructor() {
        this.app = express();
        this.config();
        this.routePrv.routes(this.app);
    }

    private config(): void {
        this.app.use(bodyParser.json());
        this.app.use(bodyParser.urlencoded({ extended: false }));
    }
}

export default new App().app.listen(PORT, () => console.log(`Example app listening on port ${PORT}!`))
