<template>
    <div class="sanction-banner-container" v-if="userSanctions.length > 0">
        <div class="sanction-banner" v-for="(sanction, index) in (showAll ? userSanctions : userSanctions.slice(0, 1))" :key="index">
            <h1 class="banner-title">
                {{ getTranslatedSanctionType(sanction) }} {{ $t('moderation.sanction.until') }} {{ sanction.endDate.toLocaleDateString() }}
            </h1>
            <p class="banner-content">
                <span>{{ $t('moderation.reason.'+ sanction.reason) }}</span>
                <br><br>
                <span>{{ sanction.message }}</span>
            </p>
            <button v-if="index === 0" class="button-toggle-show-all" @click="showAll = !showAll">
                {{ showAll ? $t('moderation.reduce') : $t('moderation.showAll') + ' (' + userSanctions.length + ')' }}
            </button>
        </div>
    </div>
    <div class="dummy_space" v-if="userSanctions.length > 0" />
</template>

<script lang="ts">
import { ModerationSanction } from "@/entities/ModerationSanction";
import { defineComponent } from "vue";

export default defineComponent({
    name: 'ModerationSanctionBanner',
    props: {
        userSanctions: {
            type: Array as () => ModerationSanction[],
            default: [] as ModerationSanction[],
            required: true
        }
    },
    data() {
        return {
            showAll: true
        };
    },
    computed: {
        bannerHeight() {
            return this.showAll ? 'auto' : '10%'; // Limite la hauteur à 10% si showAll est false
        }
    },
    methods: {
        getTranslatedSanctionType(sanction: ModerationSanction) {
            return sanction.isWarning ? this.$t('moderation.sanction.warning') : this.$t('moderation.player.banned');
        }
    }
});
</script>


<style scoped>
.sanction-banner-container {
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

.sanction-banner {
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