import { User } from "@/entities/User";
import store from "@/store";
import urlJoin from "url-join";
import ApiService from './api.service';
import { TokenService } from './storage.service';

const API_URL = import.meta.env.VITE_APP_API_URL;
const OAUTH_URL = import.meta.env.VITE_APP_OAUTH_URL;

const authorizationUrl = urlJoin(OAUTH_URL, "authorize");
const tokenUrl = urlJoin(OAUTH_URL, "token");
const callBackUrl = urlJoin(import.meta.env.VITE_APP_URL as string, "token");
const userEndPoint = urlJoin(API_URL, "users");

class AuthenticationError extends Error {
    public errorCode: number;

    constructor(errorCode: number, message: string) {
        super();
        this.name = this.constructor.name;
        this.message = message;
        this.errorCode = errorCode;
    }
}

const UserService = {
    redirectToRegister: async function(): Promise<void> {
        global.window.location.href = urlJoin(import.meta.env.VITE_ETERNALTWIN_URL, "register");
    },

    redirectToLogin: async function(): Promise<void> {
        // Generate and store a secure state parameter to prevent CSRF attacks
        const state = TokenService.generateOAuthState();
        
        const params = new URLSearchParams();
        params.set('redirect_uri', callBackUrl);
        params.set('state', state);
        
        global.window.location.href = decodeURIComponent(authorizationUrl + '?'+ params.toString());
    },

    login: async function(code: string): Promise<void> {
        try {
            await ApiService.post(tokenUrl, {
                'grant_type': 'authorization_code',
                'code': code
            });

        } catch (error: any) {
            console.error(error);
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },

    userInfo: async function(): Promise<User> {
        try {
            // Get current user info from the backend using the JWT cookie
            const response = await ApiService.get(urlJoin(userEndPoint, 'me'));
            const user = new User().load(response.data);
            TokenService.saveUserInfo(user);

            return user;
        } catch (error: any) {
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },

    /**
     * Logout the current user by clearing the httpOnly cookie and user info.
     **/
    async logout(): Promise<void> {
        try {
            await ApiService.post(urlJoin(OAUTH_URL, 'logout'));
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            TokenService.removeUserInfo();
        }
    },


    loadUser: async function(userId: string): Promise<User> {
        try {
            const response = await ApiService.get(urlJoin(userEndPoint,userId));
            const user : User = new User();

            if (response.data) {
                user.load(response.data);
            }

            return user;
        } catch (error: any) {
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },

    updateUser: async function(user: any): Promise<User> {
        if (user.userId != null) {
            const uri = urlJoin(userEndPoint, user.userId);

            await store.dispatch('gameConfig/setLoading', { loading: true });
            const response = await ApiService.patch(uri, { username: user.username, roles: user.roles } )
                .catch((e) => {
                    store.dispatch('gameConfig/setLoading', { loading: false });
                    throw e;
                });

            if (response.data) {
                user.load(response.data);
            }
            await store.dispatch('gameConfig/setLoading', { loading: false });
        }

        return user;
    },

    acceptRules: async function(): Promise<void> {
        const user = TokenService.getUserInfo();
        if (!user) {
            return;
        }

        const uri = urlJoin(userEndPoint, 'accept-rules');

        await store.dispatch('gameConfig/setLoading', { loading: true });
        await ApiService.patch(uri)
            .then(() => {
                store.dispatch('auth/userInfo');
            })
            .catch((error) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw error;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });
    },

    hasNotReadLatestNews: async function(): Promise<boolean> {
        try {
            const response = await ApiService.get(urlJoin(userEndPoint, 'has-not-read-latest-news'));
            return response.data.detail;
        } catch (error) {
            console.error(error);
            return false;
        }
    },

    readLatestNews: async function(): Promise<void> {
        try {
            await ApiService.patch(urlJoin(userEndPoint, 'read-latest-news'));
        } catch (error) {
            console.error(error);
        }
    }
};

export default UserService;

export { AuthenticationError, UserService };

