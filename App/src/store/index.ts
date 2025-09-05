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
import { NotificationService } from "@/features/notification/notification.service";
import { LocalStorageService } from "@/shared/local.storage.service";
import { translate } from "@/utils/i18n";

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
        notifications: createNotificationsModule({
            localStorageService: new LocalStorageService(),
            notificationService: new NotificationService(),
            translate
        })
    }
});
