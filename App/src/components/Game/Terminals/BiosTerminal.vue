<template>
    <div class="bios-terminal-container" v-if="terminal">
        <section class="cpu-priorities-section">
            <Tippy tag="h3">
                <img :src="getImgUrl('notes.gif')" />
                {{ terminal.sectionTitles?.cpuPriorityName }}
                <template #content>
                    <h1 v-html="formatText(terminal.sectionTitles?.cpuPriorityName)" />
                    <p v-html="formatText(terminal.sectionTitles?.cpuPriorityDescription)" />
                </template>
            </Tippy>
            <div
                class="radio-buttons-container"
                v-for="priority in terminal.infos?.availableCpuPriorities"
                :key="priority.key"
            >
                <input
                    type="radio"
                    v-model="selectedCpuPriority"
                    :value="priority.key"
                    :checked="selectedCpuPriority === priority.key"
                    :disabled="!changeNeronCpuPriorityAction.canExecute"
                    @change="executeTargetAction(terminal, changeNeronCpuPriorityAction, { cpuPriority: selectedCpuPriority })"
                >
                <label :key="priority.key">{{ priority.name }}</label>
            </div>
        </section>
        <section class="crew-lock-section">
            <Tippy tag="h3">
                <img :src="getImgUrl('notes.gif')" />
                {{ terminal.sectionTitles?.crewLockName }}
                <template #content>
                    <h1 v-html="formatText(terminal.sectionTitles?.crewLockName)" />
                    <p v-html="formatText(terminal.sectionTitles?.crewLockDescription)" />
                </template>
            </Tippy>
            <div
                class="radio-buttons-container"
                v-for="lock in terminal.infos?.availableCrewLocks"
                :key="lock.key"
            >
                <input
                    type="radio"
                    v-model="selectedCrewLock"
                    :value="lock.key"
                    :checked="selectedCrewLock === lock.key"
                    :disabled="!changeNeronCrewLockAction.canExecute"
                    @change="executeTargetAction(terminal, changeNeronCrewLockAction, { crewLock: selectedCrewLock })"
                >
                <label :key="lock.key">{{ lock.name }}</label>
            </div>
        </section>
        <section class="plasma-shield-section" v-if="togglePlasmaShieldAction">
            <Tippy tag="h3">
                <img :src="getImgUrl('notes.gif')" />
                {{ terminal.sectionTitles?.plasmaShieldName }}
                <template #content>
                    <h1 v-html="formatText(terminal.sectionTitles?.plasmaShieldName)" />
                    <p v-html="formatText(terminal.sectionTitles?.plasmaShieldDescription)" />
                </template>
            </Tippy>
            <div
                class="radio-buttons-container"
                v-for="toggle in terminal.infos?.plasmaShieldToggles"
                :key="toggle.key"
            >
                <input
                    type="radio"
                    v-model="selectedPlasmaShieldToggle"
                    :value="toggle.key"
                    :checked="selectedPlasmaShieldToggle === toggle.key"
                    :disabled="!togglePlasmaShieldAction.canExecute"
                    @change="executeTargetAction(terminal, togglePlasmaShieldAction)"
                >
                <label :key="toggle.key">{{ toggle.name }}</label>
            </div>
        </section>
        <section class="magnetic-net-section" v-if="toggleMagneticNetAction">
            <Tippy tag="h3">
                <img :src="getImgUrl('notes.gif')" />
                {{ terminal.sectionTitles?.magneticNetName }}
                <template #content>
                    <h1 v-html="formatText(terminal.sectionTitles?.magneticNetName)" />
                    <p v-html="formatText(terminal.sectionTitles?.magneticNetDescription)" />
                </template>
            </Tippy>
            <div
                class="radio-buttons-container"
                v-for="toggle in terminal.infos?.magneticNetToggles"
                :key="toggle.key"
            >
                <input
                    type="radio"
                    v-model="selectedMagneticNetToggle"
                    :value="toggle.key"
                    :checked="selectedMagneticNetToggle === toggle.key"
                    :disabled="!toggleMagneticNetAction.canExecute"
                    @change="executeTargetAction(terminal, toggleMagneticNetAction)"
                >
                <label :key="toggle.key">{{ toggle.name }}</label>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { Action } from "@/entities/Action";
import { ActionEnum } from "@/enums/action.enum";
import { mapActions } from "vuex";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "BiosTerminal",
    computed: {
        changeNeronCpuPriorityAction(): Action {
            const action = this.terminal.getActionByKey(ActionEnum.CHANGE_NERON_CPU_PRIORITY);
            if (!action) throw new Error(`No change_neron_cpu_priority action found for terminal ${this.terminal?.key}`);

            return action;
        },
        changeNeronCrewLockAction(): Action {
            const action = this.terminal.getActionByKey(ActionEnum.CHANGE_NERON_CREW_LOCK);
            if (!action) throw new Error(`No change_neron_crew_lock action found for terminal ${this.terminal?.key}`);

            return action;
        },
        togglePlasmaShieldAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.TOGGLE_PLASMA_SHIELD);
        },
        toggleMagneticNetAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.TOGGLE_MAGNETIC_NET);
        },
        target(): Terminal {
            return this.terminal;
        }
    },
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction'
        }),
        async executeTargetAction(target: Terminal, action: Action, params: object = {}): Promise<void> {
            if (action.canExecute) {
                await this.executeAction({ target, action, params });
            }
        },
        getImgUrl,
        formatText
    },
    data() {
        return {
            ActionEnum,
            selectedCpuPriority: '',
            selectedCrewLock: '',
            selectedPlasmaShieldToggle: '',
            selectedMagneticNetToggle: ''
        };
    },
    beforeMount() {
        const currentCpuPriority = this.terminal.infos?.currentCpuPriority;
        if (!currentCpuPriority) throw new Error(`No currentCpuPriority found for terminal ${this.terminal?.key}`);
        this.selectedCpuPriority = currentCpuPriority;

        const currentCrewLock = this.terminal.infos?.currentCrewLock;
        if (!currentCrewLock) throw new Error(`No currentCrewLock found for terminal ${this.terminal?.key}`);
        this.selectedCrewLock = currentCrewLock;

        const isPlasmaShieldActive = this.terminal.infos?.isPlasmaShieldActive;
        if (isPlasmaShieldActive !== null) {
            this.selectedPlasmaShieldToggle = isPlasmaShieldActive ? 'activate' : 'deactivate';
        }

        const isMagneticNetActive = this.terminal.infos?.isMagneticNetActive;
        if (isMagneticNetActive !== null) {
            this.selectedMagneticNetToggle = isMagneticNetActive ? 'active' : 'inactive';
        }
    }
});
</script>

<style lang="scss" scoped>

section {
    @extend %terminal-section;
    flex-direction: column;
    padding: 1.5em .8em .8em;
    background-image: url("/src/assets/images/bios_bg.png");

    & > p, & > div {
        margin: 0.8em 0 0;
        width: 100%;
    }

    p { text-align: left; }

    .radio-buttons-container {
        flex-direction: row;

        input {
            margin: 0 0.2em 0 0;
        }
    }
}
</style>
