import 'awilix';
import * as dotenv from 'dotenv';

dotenv.config();

import app from "./app";

const PORT = process.env.SERVER_PORT;

app.listen(PORT, () => console.log(`Example app listening on port ${PORT}!`));