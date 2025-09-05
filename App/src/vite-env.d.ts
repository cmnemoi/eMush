/// <reference types="vite/client" />

type ImportMetaEnv = {
    readonly VITE_APP_I18N_LOCALE: string
    readonly VITE_APP_I18N_FALLBACK_LOCALE: string
    readonly VITE_APP_URL: string
    readonly VITE_APP_API_URL: string
    readonly VITE_APP_OAUTH_URL: string
    readonly VITE_APP_API_RELEASE_COMMIT: string
    readonly VITE_APP_API_RELEASE_CHANNEL: string
    readonly VITE_VAPID_PUBLIC_KEY: string
}

type ImportMeta = {
    readonly env: ImportMetaEnv
}
