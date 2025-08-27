<template>
    <div class="fake-admin-page">
        <div class="panel">
            <h1 class="title">{{ $t('fakeAdmin.title') }}</h1>
            <p class="subtitle">{{ $t('fakeAdmin.subtitle', { user: getUserInfo.username }) }}</p>

            <div class="buttons center">
                <button
                    class="action-button"
                    @click="openTroll(action)"
                    v-for="action in actions"
                    :key="action"
                >
                    {{ $t(`fakeAdmin.buttons.${action}`) }}
                </button>
            </div>

            <p v-if="allClicked && !isOpen" class="hint">{{ $t('fakeAdmin.hint') }}</p>
        </div>

        <PopUp :is-open="isOpen" @close="isOpen = false">
            <h1>{{ $t('errors.title') }}</h1>
            <p>{{ popupMessage }}</p>
        </PopUp>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import PopUp from '@/components/Utils/PopUp.vue';
import { mapGetters } from 'vuex';

export default defineComponent({
    name: 'FakeAdminPage',
    components: { PopUp },
    computed: {
        ...mapGetters('auth', ['getUserInfo']),
        allClicked(): boolean {
            return this.actions.every((a: string) => this.clicked[a] === true);
        }
    },
    methods: {
        openTroll(action: string): void {
            const key = `fakeAdmin.alerts.${action}`;
            this.popupMessage = this.$t(key) as unknown as string;
            this.isOpen = true;
            this.clicked[action] = true;
        }
    },
    data() {
        return {
            isOpen: false as boolean,
            popupMessage: '' as string,
            actions: [
                'teleportToEden',
                'revealMush',
                'advanceTime50Days',
                'makeChunMush',
                'get999AP',
                'repairFuelTanks'
            ],
            clicked: {} as Record<string, boolean>
        };
    }
});
</script>

<style scoped lang="scss">
.fake-admin-page { max-width: 920px; margin: 20px auto; text-align: center; }
.panel {
  margin: 0 auto 16px;
  padding: 18px 16px 16px;
  background: radial-gradient(120% 120% at 50% 0%, #1a225b 0%, #121649 45%, #0d1036 100%);
  border: 1px solid #3965fb;
  border-radius: 8px;
  box-shadow:
    0 0 0 1px #0d1036,
    0 8px 8px -5px rgba(0,0,0,0.15),
    0 0 5px 1px rgba(57, 101, 251, 0.55);
}
.subtitle { margin: 0 0 1rem 0; opacity: .9; }
.hint { margin-top: .75rem; opacity: .75; }
.center { display: flex; justify-content: center; flex-wrap: wrap; gap: .5rem; }
.buttons { margin: .75rem 0 1rem; }
</style>


