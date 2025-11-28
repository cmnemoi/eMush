import { translate } from '@/utils/i18n';
import { App } from 'vue';
import { Store } from 'vuex';

export function createServiceWorkerListener(store: Store<any>, serviceWorker: ServiceWorkerContainer) {
    return {
        install(app: App) {
            serviceWorker.addEventListener('message', event => {
                if (event.data.type === 'PUSH_NOTIFICATION') {
                    store.dispatch('notifications/addNotification', { title: event.data.data.title, body: event.data.data.options.body });
                }
                if (event.data.type === 'NEW_VERSION') {
                    store.dispatch('toast/openWarningToast', translate("toast.newVersion"));
                }
            });
        }
    };
}
