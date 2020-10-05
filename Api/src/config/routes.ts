import express from 'express';
import {PlayerController} from '../controllers/player.controller';
import {validate, POST_PLAYER} from '../controllers/player.validator';
import {DaedalusController} from '../controllers/daedalus.controller';
import {ActionController} from '../controllers/action.controller';
import {login} from "../security/security";
import passport from "passport";
import jwt from 'jsonwebtoken';

export const PLAYER_ROUTE = '/players';
export const DAEDALUS_ROUTE = '/daedalus';
export const ACTION_ROUTE = '/action';

export class Routes {
    private playerController: PlayerController = new PlayerController();
    private daedalusController: DaedalusController = new DaedalusController();
    private actionController: ActionController = new ActionController();

    public routes(app: express.Application): void {
        // Player
        app.route(PLAYER_ROUTE).get(this.playerController.fetchAll);
        app.route(PLAYER_ROUTE + '/:id').get(this.playerController.fetch);
        app.route(PLAYER_ROUTE).post(
            validate(POST_PLAYER),
            this.playerController.post
        );
        app.route(PLAYER_ROUTE + '/:id').put(this.playerController.put);
        app.route(PLAYER_ROUTE + '/:id').patch(this.playerController.patch);
        app.route(PLAYER_ROUTE + '/:id').delete(this.playerController.delete);

        // Daedalus
        app.route(DAEDALUS_ROUTE).get(this.daedalusController.fetchAll);
        app.route(DAEDALUS_ROUTE + '/:id').get(this.daedalusController.fetch);
        app.route(DAEDALUS_ROUTE).post(this.daedalusController.post);
        app.route(DAEDALUS_ROUTE + '/:id').put(this.daedalusController.put);
        app.route(DAEDALUS_ROUTE + '/:id').patch(this.daedalusController.patch);
        app.route(DAEDALUS_ROUTE + '/:id').delete(
            this.daedalusController.delete
        );
        // Action
        app.route(ACTION_ROUTE).post(passport.authenticate('jwt', { session: false }), this.actionController.post);

        app.post(
            '/login',
            login
        );
    }
}
