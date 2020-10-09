import passport from 'passport';
import CustomStrategy from 'passport-custom';
import JWTstrategy from 'passport-jwt';
import jwt from 'jsonwebtoken';
import {Request, Response} from 'express';
import Client, {AuthorizationTokenConfig} from 'simple-oauth2';
import {logger} from '../config/logger';
import UserRepository from '../repository/user.repository';
import {User} from '../models/user.model';

interface LoggedUser {
    userid: string;
    email: string | null;
    access_token: string;
    refresh_token: string;
    expires_in: number;
}

passport.use(
    'oauth_login',
    new CustomStrategy.Strategy(async (req, done) => {
        const authorizationToken = req.body.authorizationToken;
        const client = new Client.AuthorizationCode({
            client: {
                id: process.env.OAUTH_CLIENT_ID || '',
                secret: process.env.OAUTH_CLIENT_SECRET || '',
            },
            auth: {
                tokenHost: process.env.OAUTH_CLIENT_URL || '',
                tokenPath: process.env.OAUTH_CLIENT_TOKEN || '',
            },
        });

        const tokenParams: AuthorizationTokenConfig = {
            code: authorizationToken,
            scope: process.env.OAUTH_CLIENT_SCOPE || '',
            redirect_uri: process.env.OAUTH_CLIENT_REDIRECT_URL || '',
        };

        let loggedUser: LoggedUser | null = null;
        let error = null;
        try {
            const accessToken = await client.getToken(tokenParams);
            const idTokenDecoded = jwt.decode(accessToken.token.id_token, {
                json: true,
            });
            if (idTokenDecoded !== null && idTokenDecoded.sub !== null) {
                loggedUser = {
                    userid: idTokenDecoded.sub,
                    email: idTokenDecoded.email,
                    access_token: accessToken.token.access_token,
                    refresh_token: accessToken.token.refresh_token,
                    expires_in: accessToken.token.expires_in,
                };
            }
        } catch (err) {
            error = err;
            logger.warn('Access Token Error: ' + error.message);
        }

        return done(error, loggedUser);
    })
);

passport.use(
    'dev_login',
    new CustomStrategy.Strategy(async (req, done) => {
        const username = req.body.username;

        let loggedUser: LoggedUser | null = null;
        let error = null;

        if (!username) {
            error = new Error('missing username parameter');
            logger.warn('Access Token Error: ' + error.message);
        } else {
            loggedUser = {
                userid: username,
                access_token: 'access_token',
                refresh_token: 'refresh_token',
                email: username,
                expires_in: 3600,
            };
        }

        return done(error, loggedUser);
    })
);

passport.use(
    'jwt',
    new JWTstrategy.Strategy(
        {
            secretOrKey: process.env.JWT_SECRET,
            jwtFromRequest: JWTstrategy.ExtractJwt.fromAuthHeaderAsBearerToken(),
        },
        async (token, done) => {
            try {
                const userModel = await UserRepository.findByUserId(
                    token.user.id
                );
                if (userModel !== null) {
                    return done(null, userModel);
                }
                return done(new Error('User not found'));
            } catch (error) {
                done(error);
            }
        }
    )
);

export const login = async (req: Request, res: Response): Promise<void> => {
    passport.authenticate('dev_login', async (error, user: LoggedUser) => {
        try {
            if (error || !user) {
                return res.status(401).json(error);
            }

            let userModel = await UserRepository.findByUserId(user.userid);
            if (userModel === null) {
                userModel = new User();
                userModel.userId = user.userid;
                if (user.email !== null) {
                    userModel.username = user.email;
                }

                await UserRepository.save(userModel);
            }

            req.login(user, {session: false}, async (loginError: Error) => {
                if (loginError) {
                    return res.status(401).json(loginError);
                }
                const body = {id: user.userid, expires_in: user.expires_in};
                const token = jwt.sign({user: body}, 'TOP_SECRET');
                return res.json({
                    access_token: token,
                    refresh_token: user.refresh_token,
                });
            });
        } catch (error) {
            return res.status(401).json(error);
        }
        return;
    })(req, res);
};
