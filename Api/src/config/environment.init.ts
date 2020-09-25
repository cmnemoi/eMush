import * as dotenv from 'dotenv';
import * as fs from 'fs';

// If test environment and .env.test exit; load this file
if (process.env.NODE_ENV === 'test') {
    try {
        if (fs.existsSync(__dirname + '/../.env.test')) {
            dotenv.config({path: __dirname + '/../.env.test'});
        } else {
            dotenv.config();
        }
    } catch (err) {
        console.error(err);
    }
} else {
    dotenv.config();
}
