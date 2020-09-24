import express from 'express';
import {PlayerController} from '../controllers/player.controller';
import {validate, POST_PLAYER} from '../controllers/player.validator';

export const PLAYER_ROUTE = '/players';

export class Routes {
    private playerController: PlayerController = new PlayerController();

    public routes(app: express.Application): void {
        // Player
        app.route(PLAYER_ROUTE).get(this.playerController.fetchAll);
        app.route(PLAYER_ROUTE + '/:id').get(this.playerController.fetch);
        app.route(PLAYER_ROUTE).post(validate(POST_PLAYER),this.playerController.post);
        app.route(PLAYER_ROUTE + '/:id').put(this.playerController.put);
        app.route(PLAYER_ROUTE + '/:id').patch(this.playerController.patch);
        app.route(PLAYER_ROUTE + '/:id').delete(this.playerController.delete);
    }
}
