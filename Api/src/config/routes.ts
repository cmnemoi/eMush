import { CharacterController } from "../controllers/character.controller";
import express from "express";

const route = '/characters';

export class Routes {

    private characterController: CharacterController  = new CharacterController();

    public routes(app: express.Application): void {
        app.route(route).get(this.characterController.fetchAll);
        app.route(route + '/:id').get(this.characterController.fetch);
        app.route(route).post(this.characterController.post);
        app.route(route + '/:id').put(this.characterController.put);
        app.route(route + '/:id').patch(this.characterController.patch);
        app.route(route + '/:id').delete(this.characterController.delete);
    }
}
