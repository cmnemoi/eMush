import ApiService from './api.service';
import { TokenService } from './storage.service';
import { User } from "@/entities/User";
import urlJoin from "url-join";
import store from "@/store";
import axios from "axios";

// @ts-ignore
const authorizationUrl = urlJoin(process.env.VUE_APP_OAUTH_URL, "authorize");
// @ts-ignore
const tokenUrl = urlJoin(process.env.VUE_APP_OAUTH_URL, "token");
// @ts-ignore
const callBackUrl = urlJoin(process.env.VUE_APP_URL, "token");
// @ts-ignore
const userEndPoint = urlJoin(process.env.VUE_APP_API_URL+'users');

class AuthenticationError extends Error {
    public errorCode: number

    constructor(errorCode: number, message: string) {
        super();
        this.name = this.constructor.name;
        this.message = message;
        this.errorCode = errorCode;
    }
}

const UserService = {
    redirectToLogin: async function(): Promise<void> {
        const redirectUri = new URLSearchParams();
        redirectUri.set('redirect_uri', callBackUrl);
        global.window.location.replace(decodeURIComponent(authorizationUrl + '?'+ redirectUri.toString()));
    },

    login: async function(code: string): Promise<string> {
        try {
            const response = await ApiService.post(tokenUrl, {
                'grant_type': 'authorization_code',
                'code': code
            });

            TokenService.saveToken(response.data.token);
            ApiService.setHeader();

            return response.data.token;
        } catch (error: any) {
            console.error(error);
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },


    /**
     * Refresh the access token.
     **/
    // refreshToken: async function() {
    //     const username = TokenService.getUserInfo().username;
    //     let loginData = new FormData();
    //     loginData.append("grant_type", 'password');
    //     loginData.append('username', username);
    //
    //     const requestData = {
    //         method: 'post',
    //         url: loginUrl,
    //         data: loginData,
    //     };
    //
    //     try {
    //         const response = await ApiService.customRequest(requestData);
    //
    //         TokenService.saveToken(response.data.token);
    //         // Update the header in ApiService
    //         ApiService.setHeader();
    //
    //         return response.data.token
    //     } catch (error) {
    //         throw new AuthenticationError(error.response.status, error.response.data.detail)
    //     }
    //
    // },

    userInfo: async function(): Promise<User> {
        try {
            const currentUserId = store.getters["auth/userId"];
            const response = await ApiService.get(urlJoin(userEndPoint,currentUserId));
            const user = new User();
            TokenService.saveUserInfo(user.load(response.data));

            return user;
        } catch (error: any) {
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },

    /**
     * Logout the current user by removing the token from storage.
     *
     * Will also remove `Authorization Bearer <token>` header from future requests.
     **/
    logout(): void {
        // Remove the token and remove Authorization header from Api Service as well
        TokenService.removeToken();
        TokenService.removeRefreshToken();
        TokenService.removeUserInfo();
        ApiService.removeHeader();
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
        try {
            if (user.userId != null) {
                const uri = urlJoin(userEndPoint, user.userId + '?XDEBUG_SESSION_START=PHPSTORM');

                const response = await ApiService.patch(uri, { roles: user.roles } );
                if (response.data) {
                    user.load(response.data);
                }
            }

            return user;
        } catch (error: any) {
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    }
};

export default UserService;

export { UserService, AuthenticationError };
