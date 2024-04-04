<template>
    <div class="warning-banner-container" v-if="userWarnings.length > 0">
        <div class="warning-banner" v-for="(warning, index) in (showAll ? userWarnings : userWarnings.slice(0, 1))" :key="index">
            <h1 class="banner-title">
                {{ $t('moderation.sanction.warning') }} {{ $t('moderation.sanction.until') }} {{ warning.endDate.toLocaleDateString() }}
            </h1>
            <p class="banner-content">
                <span>{{ $t('moderation.sanctionReason') }} : {{ $t('moderation.reason.'+ warning.reason) }}</span>
                <br><br>
                <span>{{ warning.message }}</span>
            </p>
            <button v-if="index === 0" class="button-toggle-show-all" @click="showAll = !showAll">
                {{ showAll ? $t('moderation.reduce') : $t('moderation.showAll') + ' (' + userWarnings.length + ')' }}
            </button>
        </div>
    </div>
    <div class="dummy_space" v-if="userWarnings.length > 0">
    </div>
</template>

<script lang="ts">
import { ModerationSanction } from "@/entities/ModerationSanction";
import { defineComponent } from "vue";

export default defineComponent({
    name: 'ModerationWarningBanner',
    props: {
        userWarnings: {
            type: Array,
            default: [] as ModerationSanction[],
            required: true
        },
    },
    data() {
        return {
            showAll: true,
        };
    },
    computed: {
        bannerHeight() {
            return this.showAll ? 'auto' : '10%'; // Limite la hauteur à 10% si showAll est false
        }
    },
});
</script>


<style scoped>
.warning-banner-container {
    position: fixed;
    bottom: 0;
    width: 100%;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    padding: 10px;
    box-sizing: border-box;
}

.dummy_space {
    position: sticky;
    bottom: 0;
    width: 100%;
    height: 50px;
    display: flex;
    flex-direction: column-reverse;
    align-items: flex-end;
    padding: 10px;
    box-sizing: border-box;
}

.warning-banner {
    background-color: #f05b76;
    color: black;
    padding: 10px;
    margin-bottom: 2px;
    border-radius: 5px;
    width: 100%;
}

.banner-title {
    margin: 0;
    font-size: 18px;
}

.banner-content {
    margin-top: 5px;
    margin-bottom: 5px;
}

.banner-content span {
    margin-right: 10px;
}
.button-toggle-show-all {
    color: #4d4d4d;
    margin-left: auto;
}
</style>
@/services/moderation_sanction.service