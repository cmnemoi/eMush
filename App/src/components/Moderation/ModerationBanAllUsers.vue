<template>
    <div class="ban-all-users-container">
        <h1>{{ $t('moderation.banAllUsers.title') }}</h1>
        <p class="description">{{ $t('moderation.banAllUsers.description') }}</p>

        <form @submit.prevent="handleSubmit">
            <div class="form-group">
                <label for="uuids">{{ $t('moderation.banAllUsers.uuidsLabel') }}</label>
                <textarea
                    id="uuids"
                    v-model="userUuids"
                    :placeholder="$t('moderation.banAllUsers.uuidsPlaceholder')"
                    rows="10"
                    required
                ></textarea>
                <small class="help-text">{{ $t('moderation.banAllUsers.uuidsHelp') }}</small>
            </div>

            <div class="form-group">
                <label for="reason">{{ $t('moderation.banAllUsers.reasonLabel') }}</label>
                <select
                    id="reason"
                    v-model="reason"
                    required
                >
                    <option v-for="reasonOption in moderationReasons" :key="reasonOption.value" :value="reasonOption.value">
                        {{ $t(reasonOption.key) }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">{{ $t('moderation.banAllUsers.messageLabel') }}</label>
                <textarea
                    id="message"
                    v-model="message"
                    :placeholder="$t('moderation.banAllUsers.messagePlaceholder')"
                    rows="4"
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label for="duration">{{ $t('moderation.banAllUsers.durationLabel') }}</label>
                <select
                    id="duration"
                    v-model="durationValue"
                    required
                >
                    <option value="permanent">{{ $t('moderation.durations.permanent') }}</option>
                    <option v-for="duration in sanctionDuration" :key="duration.value" :value="duration.value">
                        {{ $t(duration.key) }}
                    </option>
                </select>
            </div>

            <div class="form-group checkbox" v-if="durationValue === 'permanent'">
                <label for="byIp">{{ $t('moderation.byIp') }}</label>
                <input
                    id="byIp"
                    v-model="byIp"
                    type="checkbox"
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="loading">
                    {{ loading ? $t('moderation.banAllUsers.submitting') : $t('moderation.banAllUsers.submit') }}
                </button>
            </div>
        </form>

        <div v-if="result" class="result-message" :class="result.type">
            {{ result.message }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useStore } from 'vuex';
import { moderationReasons, sanctionDuration } from '@/enums/moderation_reason.enum';

const { t } = useI18n();
const store = useStore();

const userUuids = ref('');
const reason = ref('');
const message = ref('');
const durationValue = ref('permanent');
const byIp = ref(false);
const loading = ref(false);
const result = ref<{ type: 'success' | 'error', message: string } | null>(null);

const durationDays = computed(() => {
    if (durationValue.value === 'permanent') {
        return null;
    }
    const match = durationValue.value.match(/P(\d+)D/);
    return match ? parseInt(match[1]) : null;
});

const parseUuids = (text: string): string[] => {
    return text
        .split(/[\n,;]+/)
        .map(uuid => uuid.trim())
        .filter(uuid => uuid.length > 0);
};

const handleSubmit = async () => {
    result.value = null;
    const uuids = parseUuids(userUuids.value);

    if (uuids.length === 0) {
        result.value = {
            type: 'error',
            message: t('moderation.banAllUsers.noUuidsError')
        };
        return;
    }

    loading.value = true;

    const success = await store.dispatch('moderation/banAllUsers', {
        userUuids: uuids,
        reason: reason.value,
        message: message.value,
        durationDays: durationDays.value,
        byIp: byIp.value
    });

    if (success) {
        result.value = {
            type: 'success',
            message: t('moderation.banAllUsers.success', { count: uuids.length })
        };

        userUuids.value = '';
        reason.value = '';
        message.value = '';
        durationValue.value = 'permanent';
    } else {
        result.value = {
            type: 'error',
            message: t('moderation.banAllUsers.error')
        };
    }

    loading.value = false;
};
</script>

<style scoped lang="scss">
.ban-all-users-container {
    max-width: 800px;
    margin: 2em auto;
    padding: 2em;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;

    h1 {
        color: #fff;
        margin-bottom: 0.5em;
    }

    .description {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2em;
    }

    .form-group {
        margin-bottom: 1.5em;

        label {
            display: block;
            color: #fff;
            margin-bottom: 0.5em;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.75em;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: #fff;
            font-family: inherit;
            font-size: 1em;

            &::placeholder {
                color: rgba(255, 255, 255, 0.4);
            }

            &:focus {
                outline: none;
                border-color: rgba(15, 89, 171, 0.8);
                background: rgba(255, 255, 255, 0.15);
            }
        }

        select {
            cursor: pointer;

            option {
                background: #1a1a1a;
                color: #fff;
            }
        }

        textarea {
            resize: vertical;
            font-family: monospace;
        }

        .help-text {
            display: block;
            margin-top: 0.5em;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9em;
        }

        &.checkbox {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;

            label {
                margin-bottom: 0;
                margin-right: 1em;
            }

            input[type="checkbox"] {
                width: auto;
            }
        }
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 2em;

        .btn-primary {
            padding: 0.75em 2em;
            background: rgba(15, 89, 171, 0.8);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;

            &:hover:not(:disabled) {
                background: rgba(15, 89, 171, 1);
            }

            &:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        }
    }

    .result-message {
        margin-top: 1.5em;
        padding: 1em;
        border-radius: 4px;
        font-weight: bold;

        &.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            color: #a5d6a7;
        }

        &.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
            color: #ef9a9a;
        }
    }
}
</style>

