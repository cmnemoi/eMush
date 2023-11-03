<template>
    <div class="daedalus-alarms" v-if="alerts.length > 0">
        <p v-if="!loadingAlerts" :class="{ alarm: !isNoAlert }">
            <Tippy v-if="isNoAlert">
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
        </p>
    </div>
</template>

<script lang="ts">
import { AlertsIcons, NO_ALERT } from "@/enums/alerts.enum";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { Alert } from "@/entities/Alerts";
import { mapGetters } from "vuex";

interface AlertsState {
    loading: boolean,
    alerts: Alert[]
}

export default defineComponent ({
    name: "Alerts",
    computed: {
        ...mapGetters('daedalus', [
            'alerts',
            'loadingAlerts'
        ]),
        isNoAlert: function (): boolean {
            return this.alerts.length === 0 || (this.alerts.length === 1 && (this.alerts[0].key ?? '') === NO_ALERT);
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
            return AlertsIcons[alert.key];
        },
        formatAlert(value: string): string {
            if (! value) return '';
            return formatText(value.toString());
        }
    }
});
</script>

<style scoped lang="scss">

$redAlert: #ff4e64;

.daedalus-alarms p{
    display: flex;
    align-items: center;
    flex-direction: row;
    padding: 3px 6px 1px 6px;
    margin: 0;
    max-height: 25px;
    color: white;
    letter-spacing: 0.03em;
    font-variant: small-caps;
    font-weight: 400;
    border: 1px solid $greyBlue;
    border-radius: 3px;
    background: $greyBlue;
    box-shadow: 0 0 5px 1px inset rgba(28, 29, 56, 1);
    text-shadow: 0 0 2px rgba(0, 0, 0, 1), 0 0 2px rgba(0, 0, 0, 1); /* twice the same shadow */

    *:not(:last-child) {
        margin-right: 10px;
    }

    span img {
            position: relative;
            top: -0.1em;
    }

    &.alarm {
        color: $redAlert;
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
