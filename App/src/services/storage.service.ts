import { User } from "../entities/User";

const TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
const USER_INFO = 'user_info';

/**
 * Manage how Access Tokens are being stored and retrieved from storage.
 *
 * Current implementation stores to localStorage. Local Storage should always be
 * accessed through this instance.
 **/
const TokenService = {
    getToken(): string | null {
        return localStorage.getItem(TOKEN_KEY);
    },

    saveToken(accessToken: string) : void{
        localStorage.setItem(TOKEN_KEY, accessToken);
    },

    removeToken(): void {
        localStorage.removeItem(TOKEN_KEY);
    },

    getRefreshToken(): string | null {
        return localStorage.getItem(REFRESH_TOKEN_KEY);
    },

    saveRefreshToken(refreshToken: string): void {
        localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken);
    },

    removeRefreshToken(): void {
        localStorage.removeItem(REFRESH_TOKEN_KEY);
    },

    getUserInfo(): User|null {
        const user = new User();
        const storedUserInfo = localStorage.getItem(USER_INFO);
        if (storedUserInfo !== null) {
            return user.decode(storedUserInfo);
        }
        return null;
    },

    saveUserInfo(user: User): void {
        localStorage.setItem(USER_INFO, user.jsonEncode());
    },

    removeUserInfo(): void {
        localStorage.removeItem(USER_INFO);
    }

};

export { TokenService };
