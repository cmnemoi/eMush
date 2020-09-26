import './config/environment.init'; // Needs to be imported first, contains environment variable
import express from 'express';
import * as bodyParser from 'body-parser';
import {Routes} from './config/routes';
import dbInit from './config/database.init';
import {logger} from './config/logger';

const PORT = process.env.SERVER_PORT;

class App {
    public app: express.Application;
    public routePrv: Routes = new Routes();

    constructor() {
        dbInit();
        this.app = express();
        this.config();
        this.routePrv.routes(this.app);
    }

    private config(): void {
        this.app.use(bodyParser.json());
        this.app.use(bodyParser.urlencoded({extended: false}));
    }
}

export default new App().app.listen(PORT, () =>
    logger.info(`Application listening on port ${PORT}!`)
);
