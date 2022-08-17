<template>
    <div class="daedalus_detail">
        <div class="daedalus_detail_title">
            Daedalus:
        </div>
        <div class="daedalus_detail_content">
            <div class="flex-row">
                <div class="grow">
                    <label for="daedalus_name">{{ $t('daedalus.name') }}</label>
                    <input
                        id="daedalus_name"
                        ref="daedalus_name"
                        v-model="name"
                        type="text"
                    >
                </div>
            </div>
        </div>
        <button class="button" type="submit" @click="save">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import UserService from "@/services/user.service";
import { User } from "@/entities/User";
import { UserRole } from "@/enums/user_role.enum";
import DaedalusService from "@/services/daedalus.service";

export default defineComponent({
    name: "DaedalusDetailPage",
    data() {
        return {
            name: ''
        };
    },
    methods: {
        save(): void {
            DaedalusService.createDaedalus(this.name);
        },
    },
});
</script>

<style lang="scss" scoped>
.user_detail_content {
    padding-top: 20px;
}
</style>
