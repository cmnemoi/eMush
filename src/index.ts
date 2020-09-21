import express from "express";
import dotenv from "dotenv";

// initialize configuration
dotenv.config();

const app = express();
const port = process.env.SERVER_PORT;

// define a route handler for the default home page
app.get( "/", ( req, res ) => {
    res.send( "Hello world!" );
} );

// start the Express server
app.listen( port, () => {
    // tslint:disable-next-line:no-console
    console.log( `server started at http://localhost:${ port }` );
} );