<template>
    <div v-if="offlineReady || needRefresh" class="pwa-toast">
        <div class="message">
            <span v-if="offlineReady">
                App ready to work offline
            </span>
            <span v-else>
                New content available, click on reload button to update.
            </span>
        </div>
        <button v-if="needRefresh" @click="updateServiceWorker">
            Reload
        </button>
        <button @click="close">
            Close
        </button>
    </div>
</template>

<script>
import { useRegisterSW } from "virtual:pwa-register/vue";

export default {
    name: "ReloadPWA",
    setup() {
        const { offlineReady, needRefresh, updateServiceWorker } = useRegisterSW();

        const close = async () => {
            offlineReady.value = false;
            needRefresh.value = false;
        };

        return { offlineReady, needRefresh, updateServiceWorker, close };
    }
};
</script>
