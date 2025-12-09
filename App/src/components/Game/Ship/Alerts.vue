<template>
    <div v-if="!loadingAlerts" :class="['daedalus-state', isNoAlert ? 'calm' : 'alerts']">
        <Tippy v-if="isNoAlert" class="no-alert">
            <img :src="alertIcon(alerts[0])">{{ alerts[0].name }}
            <template #content>
                <h1>{{ alerts[0].name }}</h1>
                <p v-html="formatAlert(alerts[0].description)"/>
            </template>
        </Tippy>
        <span v-else>{{ alerts[0].prefix }}</span>
        <Tippy v-for="(alert, key) in alertsDisplayed" :key="key">
            <img
                :src="alertIcon(alert)"
                :alt="alert.name"
            >
            <template #content>
                <h1>{{ alert.name }}</h1>
                <p v-html="formatAlert(alert.description)" />
                <ul v-if="alert.reports.length > 0" style="flex-direction:column">
                    <li v-for="(report, reportKey) in alert.reports" :key="reportKey">
                        <p v-html="formatAlert(report)"/>
                    </li>
                </ul>
            </template>
        </Tippy>
    </div>
</template>

<script lang="ts">
import { AlertsIcons, AlertEnum } from "@/enums/alerts.enum";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { Alert } from "@/entities/Alerts";
import { mapGetters } from "vuex";
import { characterEnum } from "@/enums/character";

export default defineComponent ({
    name: "Alerts",
    computed: {
        ...mapGetters('daedalus', [
            'alerts',
            'loadingAlerts'
        ]),
        isNoAlert: function (): boolean {
            return this.alerts.length === 0 || (this.alerts.length === 1 && (this.alerts[0].key ?? '') === AlertEnum.NO_ALERT);
        },
        alertsDisplayed: function (): Alert[] {
            if (this.isNoAlert) {
                return [];
            }

            return this.alerts;
        }
    },
    methods: {
        alertIcon: function (alert: Alert): string {
            if (alert.lostPlayer) {
                return characterEnum[alert.lostPlayer].head;
            }
            return AlertsIcons[alert.key];
        },
        formatAlert(value: string): string {
            return formatText(value);
        }
    }
});
</script>

<style scoped lang="scss">

$redAlert: #ff4e64;

.daedalus-state {
    display: flex;
    flex-direction: row;
    gap: 10px;
    width: auto;
    white-space: nowrap;
    padding: 2px 6px 2px 6px;
    max-height: 25px;
    font-variant: small-caps;
    letter-spacing: 0.03em;
    background: $greyBlue;
    border-radius: 3px;
    box-shadow: 0 0 5px 1px inset rgba(28, 29, 56, 1);
    text-shadow: 0 0 2px rgba(0, 0, 0, 1), 0 0 2px rgba(0, 0, 0, 1); /* twice the same shadow */

    &.calm {
        color: white;
        font-weight: 400;
        border: 1px solid $greyBlue;

        span:nth-child(1) {
            display: flex;
            gap: 2px;
            vertical-align: middle;
        }
    }

    &.alerts {
        color: $redAlert;
        max-height: 25px;
        font-weight: 700;
        animation: alarms-border-color 0.85s ease-in-out infinite;
    }
}

@keyframes alarms-border-color {
    0% { border: 1px solid $redAlert; }
    50% { border: 1px solid $greyBlue; }
    100% { border: 1px solid $redAlert; }
}

</style>
