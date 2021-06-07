<template>
    <div class="daedalus-alarms">
        <p v-if="!loading" class="calme">
            <span v-if="isNoAlert">
                <img :src="alertIcon(alerts[0])">{{ alerts[0].name }}
            </span>
            <span v-else>Alertes :</span>
            <Tooltip v-for="(alert, key) in alertsDisplayed" :key="key">
                <template #tooltip-trigger>
                    <img
                        :src="alertIcon(alert)"
                        :alt="alert.name"
                    >
                </template>
                <template #tooltip-content>
                    <h1>{{ alert.name }}</h1>
                    <p>{{ alert.description }}</p>
                    <ul v-if="alert.reports.length > 0">
                        <li v-for="(report, reportKey) in alert.reports" :key="reportKey">
                            {{ report }}
                        </li>
                    </ul>
                </template>
            </Tooltip>
        </p>
    </div>
</template>

<script>
import { Daedalus } from "@/entities/Daedalus";
import DaedalusService from "@/services/daedalus.service";
import { AlertsIcons, NO_ALERT } from "@/enums/alerts.enum";
import Tooltip from "../../Utils/ToolTip";

export default {
    name: "Alerts",
    components: {Tooltip},
    props: {
        daedalus: Daedalus
    },
    data: function () {
        return {
            loading: false,
            alerts: []
        };
    },
    computed: {
        isNoAlert: function () {
            return this.alerts.length === 0 || (this.alerts.length === 1 && this.alerts[0].key === NO_ALERT);
        },
        alertsDisplayed: function () {
            if (this.isNoAlert) {
                return [];
            }

            return this.alerts;
        }
    },
    beforeMount() {
        this.loading = true;
        DaedalusService.loadAlerts(this.daedalus).then((res) => {
            this.loading = false;
            this.alerts = res;
        });
    },
    methods: {
        alertIcon: function (alert) {
            return AlertsIcons[alert.key];
        }
    }
};
</script>

<style scoped lang="scss">
.daedalus-alarms p {
    display: flex;
    align-items: center;
    flex-direction: row;
    padding: 3px 6px 1px 6px;
    margin: 0;
    max-height: 25px;
    color: white;
    font-size: 1em;
    font-weight: 400;
    border: 1px solid rgba(58, 106, 171, 1);;
    border-radius: 3px;
    background: rgba(58, 106, 171, 1);
    box-shadow: 0 0 5px 1px inset rgba(28, 29, 56, 1);
    text-shadow: 0 0 2px rgba(0, 0, 0, 1), 0 0 2px rgba(0, 0, 0, 1); /* twice the same shadow */

    *:not(:last-child) {
        margin-right: 10px;
    }

    span img {
        vertical-align: top;
    }

    &.alarm {
        color: #ff4e64;
        font-weight: 700;
        animation: alarms-border-color 0.85s ease-in-out infinite; /* keyframes at the end of the doc */
    }
}

</style>
