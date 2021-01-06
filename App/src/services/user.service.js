import ApiService from './api.service';
import { TokenService } from './storage.service';
import { User } from "@/entities/User";

const authorizationUrl = process.env.VUE_APP_OAUTH_URL + '/authorize';
const tokenUrl = process.env.VUE_APP_OAUTH_URL + '/token';
const callBackUrl = process.env.VUE_APP_URL + '/token';

class AuthenticationError extends Error {
    constructor(errorCode, message) {
        super();
        this.name = this.constructor.name;
        this.message = message;
        this.errorCode = errorCode;
    }
}

const UserService = {
    redirect: async function(passphrase) {
        const redirectUri = new URLSearchParams();
        console.log(passphrase);
        redirectUri.set('redirect_uri', callBackUrl);
        redirectUri.set('passphrase', passphrase);
        global.window.location.replace(decodeURIComponent(authorizationUrl + '?'+ redirectUri.toString()));
    },

    login: async function(code) {
        try {
            const response = await ApiService.post(tokenUrl, {
                'grant_type': 'authorization_code',
                'code': code
            });

            TokenService.saveToken(response.data.token);
            ApiService.setHeader();

            ApiService.mount401Interceptor();

            return response.data.token;
        } catch (error) {
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

    userInfo: async function() {
        try {
            let params = {
                header: {
                    'accept' : 'application/json'
                }
            };
            const response = await ApiService.get(process.env.VUE_APP_API_URL+'users', params);
            let user = new User();
            TokenService.saveUserInfo(user.load(response.data));

            return user;
        } catch (error) {
            // eslint-disable-next-line no-console
            throw new AuthenticationError(error.response.status, error.response.data.detail);
        }
    },

    /**
     * Logout the current user by removing the token from storage.
     *
     * Will also remove `Authorization Bearer <token>` header from future requests.
     **/
    logout() {
        // Remove the token and remove Authorization header from Api Service as well
        TokenService.removeToken();
        TokenService.removeRefreshToken();
        TokenService.removeUserInfo();
        ApiService.removeHeader();

        // NOTE: Again, we'll cover the 401 Interceptor a bit later.
        ApiService.unmount401Interceptor();
    }
};

export default UserService;

export { UserService, AuthenticationError };
