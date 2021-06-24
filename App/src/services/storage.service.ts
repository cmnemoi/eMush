import { User } from "../entities/User";

const TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
const USER_INFO = 'user_info';

/**
 * Manage the how Access Tokens are being stored and retreived from storage.
 *
 * Current implementation stores to localStorage. Local Storage should always be
 * accessed through this instace.
 **/
const TokenService = {
    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    },

    saveToken(accessToken: string) {
        localStorage.setItem(TOKEN_KEY, accessToken);
    },

    removeToken() {
        localStorage.removeItem(TOKEN_KEY);
    },

    getRefreshToken() {
        return localStorage.getItem(REFRESH_TOKEN_KEY);
    },

    saveRefreshToken(refreshToken: string) {
        localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken);
    },

    removeRefreshToken() {
        localStorage.removeItem(REFRESH_TOKEN_KEY);
    },

    getUserInfo() {
        let user = new User();
        const storedUserInfo = localStorage.getItem(USER_INFO);
        if (storedUserInfo !== null) {
            return user.decode(storedUserInfo);
        }
        return null;
    },

    saveUserInfo(user: User) {
        localStorage.setItem(USER_INFO, user.jsonEncode());
    },

    removeUserInfo() {
        localStorage.removeItem(USER_INFO);
    }

};

export { TokenService };
