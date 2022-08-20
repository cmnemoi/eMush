<template>
    <div v-if="user" class="user_detail">
        <div class="user_detail_title">
            User: {{ user.userId }}
        </div>
        <div class="user_detail_content">
            <div class="flex-row">
                <div class="grow">
                    <label for="user_username">{{ $t('user.username') }}</label>
                    <input
                        id="user_username"
                        ref="user_username"
                        v-model="user.username"
                        type="text"
                        readonly
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="user_roles">{{ $t('user.roles') }}</label>
                    <select v-model="user.roles" multiple>
                        <option v-for="option in rolesOption" v-bind:value="option.key" :key="option.key">
                            {{ option.text }}
                        </option>
                    </select>
                    <ErrorList v-if="errors.roles" :errors="errors.roles"></ErrorList>
                </div>
            </div>
        </div>
        <button class="button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import UserService from "@/services/user.service";
import { User } from "@/entities/User";
import { UserRole } from "@/enums/user_role.enum";
import { handleErrors } from "@/utils/apiValidationErrors";
import ErrorList from "@/components/Utils/ErrorList.vue";

interface UserDetailData {
    user: User | null,
    errors: any,
    rolesOption: object[],
}

export default defineComponent({
    name: "UserDetailPage",
    components: {
        ErrorList
    },
    data() : UserDetailData {
        return {
            user: null,
            errors: {},
            rolesOption: [
                {
                    key: UserRole.USER,
                    text: 'User',
                },
                {
                    key: UserRole.MODERATOR,
                    text: 'Moderator',
                },
                {
                    key: UserRole.ADMIN,
                    text: 'Admin',
                },
                {
                    key: UserRole.SUPER_ADMIN,
                    text: 'Super Admin',
                },
                {
                    key: 'error',
                    text: 'Super Admin error',
                },
            ],
        };
    },
    methods: {
        update(): void {
            UserService.updateUser(this.user)
                .then((result: User) => (this.user = result))
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
                        }
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.error(error.request);
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.error('Error', error.message);
                    }
                });
            ;
        },
    },
    beforeMount() {
        const userId = this.$route.params.userId;
        if (typeof userId === 'string') {
            UserService.loadUser(userId)
                .then((result: User) => (this.user = result))
            ;
        }
    }
});
</script>

<style lang="scss" scoped>
.user_detail_content {
    padding-top: 20px;
}
</style>
