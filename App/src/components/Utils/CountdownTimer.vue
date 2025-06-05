<template>
    <div>
        <slot
            :hour="formattedHours"
            :min="formattedMinutes"
            :sec="formattedSeconds"
            :is-finished="isCountdownFinished"
        />
    </div>
</template>

<script>
const MILLISECONDS_PER_SECOND = 1000;
const SECONDS_PER_MINUTE = 60;
const SECONDS_PER_HOUR = 3600;
const TIMER_UPDATE_INTERVAL_MS = 1000;
const MINIMUM_DOUBLE_DIGIT_NUMBER = 10;

export default {
    name: 'CountdownTimer',

    props: {
        endDate: {
            type: Date,
            default() {
                return new Date();
            }
        },
        shouldAllowNegativeCountdown: {
            type: Boolean,
            default: false
        }
    },

    emits: ['end-time'],

    data() {
        return {
            currentTime: new Date(),
            timerIntervalId: null,
            isCountdownFinished: false
        };
    },

    computed: {
        remainingTimeInSeconds() {
            return Math.trunc((this.endDate - this.currentTime) / MILLISECONDS_PER_SECOND);
        },

        formattedHours() {
            const hours = Math.trunc(this.remainingTimeInSeconds / SECONDS_PER_HOUR);
            return hours;
        },

        formattedMinutes() {
            const minutes = Math.trunc(this.remainingTimeInSeconds / SECONDS_PER_MINUTE) % SECONDS_PER_MINUTE;
            return this.formatTimeUnit(minutes);
        },

        formattedSeconds() {
            const seconds = this.remainingTimeInSeconds % SECONDS_PER_MINUTE;
            return this.formatTimeUnit(seconds);
        }
    },

    watch: {
        endDate: {
            immediate: true,
            handler(newEndDate) {
                this.resetTimer();
                this.startCountdown(newEndDate);
            }
        }
    },

    beforeUnmount() {
        this.clearTimerInterval();
    },

    methods: {
        formatTimeUnit(timeUnit) {
            const isDoubleDigit = timeUnit >= MINIMUM_DOUBLE_DIGIT_NUMBER;
            return isDoubleDigit ? timeUnit : '0' + timeUnit;
        },

        resetTimer() {
            this.clearTimerInterval();
            this.isCountdownFinished = false;
        },

        startCountdown(targetEndDate) {
            this.timerIntervalId = setInterval(() => {
                this.updateCurrentTime();
                this.checkIfCountdownShouldFinish(targetEndDate);
            }, TIMER_UPDATE_INTERVAL_MS);
        },

        updateCurrentTime() {
            this.currentTime = new Date();
        },

        checkIfCountdownShouldFinish(targetEndDate) {
            if (this.shouldAllowNegativeCountdown) {
                return;
            }

            const hasTimeExpired = this.currentTime > targetEndDate;
            if (hasTimeExpired) {
                this.finishCountdown(targetEndDate);
            }
        },

        finishCountdown(targetEndDate) {
            this.currentTime = targetEndDate;
            this.isCountdownFinished = true;
            this.emitEndTimeEvent();
            this.clearTimerInterval();
        },

        emitEndTimeEvent() {
            this.$emit('end-time');
        },

        clearTimerInterval() {
            if (this.timerIntervalId) {
                clearInterval(this.timerIntervalId);
                this.timerIntervalId = null;
            }
        }
    }
};
</script>
