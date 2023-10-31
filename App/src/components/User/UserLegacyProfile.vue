<template>
    <div v-if="user?.legacyUser" :class="user?.legacyUser?.hidden ? 'infos hidden' : 'infos'">
        <h3 @click="user?.legacyUser?.toggle()">{{ $t('import.importedProfile', {username: user?.legacyUser?.twinoidUsername, id: user?.legacyUser?.twinoidId}) }}</h3>
        <pre v-html="user?.legacyUser?.jsonEncode()"></pre>
    </div>
    <div v-else class="infos flex-row">
        {{ $t('import.noImportedProfile') }}
        <router-link class="action-button" :to="{ name: 'ImportPage' }">{{ $t('import.importMyProfile') }}</router-link>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import UserService from "@/services/user.service";
import { User } from "@/entities/User";

interface UserData {
    user: User | null,
}

export default defineComponent ({
    name: "UserLegacyProfile",
    methods: {
        getUser(id: string) {
            UserService.loadUser(id).then((user) => {
                this.user = user;
            }).catch((error) => {
                console.error(error);
            });
        }
    },
    data() : UserData {
        return {
            user: null
        };
    },
    beforeMount() {
        const userId = this.$route.params.userId;
        if (typeof userId == 'string') {
            this.getUser(userId);
        }
    }
});

</script>

<style lang="scss" scoped>

.action-button {
    @include button-style();
}
.infos {
    border: 1px solid #576077;
    background-color: #222b6b;
    box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.4);
    padding: 1.2em;
    margin: .4em;

    img {
        width: fit-content;
        height: fit-content;
    }

    p { line-height: 1.4em; }

    span {
        ::v-deep(a) {
            color: $green;
        }
    }

    h3 {
        cursor: pointer;
    }
}
.hidden {
    opacity: 0.75;

    pre { display: none; }
}
</style>
