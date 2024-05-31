<template>
    <div class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <button class="close-button" @click="closeModal">X</button>
            <h2>Détails de la sanction</h2>

            <!-- First Line: user and playerId with buttons -->
            <div class="modal-row">
                <div class="modal-field">
                    <strong>User:</strong> {{ sanctionDetails.user }}
                    <div class="user-buttons">
                        <router-link
                            :to="{ name: 'SanctionListPage', params: { username: sanctionDetails.userId, userId : sanctionDetails.userId } }">
                            {{ $t('moderation.sanctionList') }}
                        </router-link>
                        <router-link
                            :to="{ name: 'ModerationUserListUserPage', params: { userId : sanctionDetails.userId } }">
                            {{ $t('moderation.goToUserProfile') }}
                        </router-link>
                    </div>
                </div>
                <div class="modal-field">
                    <div v-if="sanctionDetails.playerId">
                        <strong>Player ID:</strong> {{ sanctionDetails.playerId }}
                        <router-link
                            :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: sanctionDetails.playerId } }">
                            {{ $t("moderation.goToPlayerDetails") }}
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Second Line: moderationAction and reason -->
            <div class="modal-row">
                <div class="modal-field">
                    <strong>Moderation Action:</strong> {{ sanctionDetails.moderationAction }}
                </div>
                <div class="modal-field">
                    <strong>Reason:</strong> {{ sanctionDetails.reason }}
                </div>
            </div>

            <!-- Third Line: message -->
            <div class="modal-row">
                <div class="modal-field">
                    <strong>Message:</strong> {{ sanctionDetails.message }}
                </div>
            </div>

            <!-- Fourth Line: author -->
            <div class="modal-row">
                <div class="modal-field">
                    <strong>Author:</strong> {{ sanctionDetails.author }}
                </div>
            </div>

            <!-- Fifth Line: startDate and endDate with conditional color -->
            <div class="modal-row">
                <div class="modal-field" :class="{ active: sanctionDetails.isActive, inactive: !sanctionDetails.isActive }">
                    <strong>Start Date:</strong> {{ sanctionDetails.startDate }}
                </div>
                <div class="modal-field" :class="{ active: sanctionDetails.isActive, inactive: !sanctionDetails.isActive }">
                    <strong>End Date:</strong> {{ sanctionDetails.endDate }}
                </div>
            </div>

            <!-- Sixth Line: action buttons -->
            <div class="modal-row actions">
                <button class="action-button" @click="removeSanction(sanctionDetails.id)">Supprimer la sanction</button>
                <button class="action-button" @click="suspendSanction(sanctionDetails.id)">Suspendre la sanction</button>
            </div>
        </div>
    </div>
</template>

<script>
import { ModerationSanction } from "@/entities/ModerationSanction.js";

export default {
    name: 'ModerationSanctionDetail',
    props: {
        sanctionDetails: {
            type: ModerationSanction,
            required: true
        },
        showModal: {
            type: Boolean,
            required: true
        }
    },
    methods: {
        closeModal() {
            this.$emit('close');
        },
        removeSanction(sanctionId) {
            this.$emit('remove', sanctionId);
        },
        suspendSanction(sanctionId) {
            this.$emit('suspend', sanctionId);
        }
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
}

.modal-content {
    background: #222b6b;
    padding: 20px;
    border-radius: 5px;
    width: 90%;
    max-width: 1000px;
    position: relative;
    overflow-y: auto;
    max-height: 90vh;
}

.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
}

.modal-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    width: 100%;
}

.modal-field {
    flex: 1;
    min-width: 100px;
    margin-right: 10px;
    width: 40%;
}

.user-buttons {
    display: flex;
    gap: 10px;
}

.actions {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.action-button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px;
    margin: 5px;
    cursor: pointer;
}

.action-button:hover {
    background-color: #0056b3;
}

.active {
    color: green;
}

.inactive {
    color: red;
}
</style>
