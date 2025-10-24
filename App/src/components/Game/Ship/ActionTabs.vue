<template>
    <div class="action-tabs">
        <ul>
            <Tippy
                tag="li"
                v-for="tab in visibleTabs"
                :key="tab.key"
                :class="{ checked: isSelected(tab) }"
            >
                <button @click="changeTab(tab)">
                    <img :src="tab.icon" :alt="tab.name">
                </button>
                <template #content>
                    <h1 v-html="formatText(tab.name)" />
                    <p v-html="formatText(tab.description)" />
                </template>
            </Tippy>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { Action } from '@/entities/Action';
import { Equipment } from '@/entities/Equipment';
import { Hunter } from '@/entities/Hunter';
import { Player } from '@/entities/Player';
import { formatText } from '@/utils/formatText';
import { getImgUrl } from '@/utils/getImgUrl';
import { translate } from '@/utils/i18n';
import { computed } from 'vue';

export type ActionType = 'human' | 'mush' | 'admin'
type ActionWithTarget = {
    action: Action,
    target: Equipment | Player | Hunter
}
type ActionTab = {
    key: ActionType;
    name: string;
    description: string;
    icon: string;
}

const props = defineProps<{
    targetActionsMush: ActionWithTarget[] | Action[];
    targetActionsAdmin: ActionWithTarget[] | Action[];
}>();

const isSelected = (tab: ActionTab) => activeTab.value === tab.key;
const visibleTabs = computed(() => {
    return tabs.filter(tab => {
        if (tab.key === 'mush') return props.targetActionsMush.length !== 0;
        if (tab.key === 'admin') return props.targetActionsAdmin.length !== 0;
        return true;
    });
});

const changeTab = (tab: ActionTab) => activeTab.value = tab.key;

const activeTab = defineModel<ActionType>('activeTab', { required: true });
const tabs: ActionTab[] = [
    {
        key: 'human',
        name: translate('actionTabs.human.name'),
        description: translate('actionTabs.human.description'),
        icon: getImgUrl('status/multi.png')
    },
    {
        key: 'mush',
        name: translate('actionTabs.mush.name'),
        description: translate('actionTabs.mush.description'),
        icon: getImgUrl('status/berzerk.png')
    },
    {
        key: 'admin',
        name: translate('actionTabs.admin.name'),
        description: translate('actionTabs.admin.description'),
        icon: getImgUrl('ui_icons/noob.png')
    }
];
</script>

<style lang="scss" scoped>
.action-tabs {
    position: relative;
    ul {
        display: flex;
        flex-direction: row;
        justify-content: center;
    }

    li {
        display: flex;
        flex: 1;
        height: 22px;
        margin-right: 5px;
        margin-left: 2px;
        max-width: 50px;

        background: rgb(23, 30, 82);
        @include corner-bezel(4.5px, 4.5px, 0);

        &.checked,
        &:hover,
        &:focus {
            background: rgb(50, 79, 143);
        }


        button {
            & {
                display: flex;
                flex : 1;
                justify-content: center;
            }

            img {
                position: relative;
                top: 4px;
                width: 16px;
                height: 16px;
            }
        }
    }
}
</style>
