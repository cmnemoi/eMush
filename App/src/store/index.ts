import { gameConfig } from "@/store/game_config.module";
import { action } from "@/store/action.module";
import { auth } from "@/store/auth.module";
import { createStore } from 'vuex';
import { error } from "@/store/error.module";
import { player } from "@/store/player.module";
import { room } from "@/store/room.module";
import { communication } from "@/store/communication.module";
import { daedalus } from "@/store/daedalus.module";
import { admin } from "@/store/admin.module";
import { popup } from "@/store/popup.module";
import { moderation } from "@/store/moderation.module";
import { toast } from "@/store/toast.module";
import { adminActions } from "@/store/admin.actions.module";
import { locale } from "@/store/locale.module";
import { createNotificationsModule } from "@/features/notification/store";
import { createSettingsModule } from "@/features/settings/store";
import { NotificationService } from "@/features/notification/notification.service";
import { LocalStorageService } from "@/shared/local.storage.service";
import { translate } from "@/utils/i18n";
import { createDaedalusRankingModule } from "@/features/rankings/store";
import { gateway as daedalusRankingGateway } from "@/features/rankings/gateway";
import { createUserProfileModule } from "@/features/userProfile/store";
import { gateway as userProfileGateway } from "@/features/userProfile/gateway";
import { createAchievementsModule } from "@/features/achievements/store";
import { achievementsGateway } from "@/features/achievements/gateway";
import { createCharacterBiographyModule } from "@/features/biography/store";
import { BiographyService } from "@/services/biography.service";

export default createStore({
    modules: {
        gameConfig,
        action,
        auth,
        error,
        player,
        room,
        communication,
        daedalus,
        admin,
        popup,
        moderation,
        toast,
        adminActions,
        locale,
        achievements: createAchievementsModule(achievementsGateway),
        notifications: createNotificationsModule({
            localStorageService: new LocalStorageService(),
            notificationService: new NotificationService(),
            translate
        }),
        settings: createSettingsModule({
            localStorageService: new LocalStorageService()
        }),
        daedalusRanking: createDaedalusRankingModule(daedalusRankingGateway.loadDaedalusRanking),
        userProfile: createUserProfileModule(
            userProfileGateway.loadShipsHistory,
            // Lazy load UserService to avoid circular dependency
            async (userId: string) => {
                const UserService = (await import("@/services/user.service")).default;
                return UserService.loadUser(userId);
            }
        ),
        biography: createCharacterBiographyModule(
            BiographyService.loadCharacterBiography
        )
    }
});
