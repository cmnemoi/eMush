import GameConfigListPage from "@/components/Admin/Config/GameConfig/GameConfigListPage.vue";
import GameConfigDetailPage from "@/components/Admin/Config/GameConfig/GameConfigDetailPage.vue";
import ModifierConfigListPage from "@/components/Admin/Config/ModifierConfig/ModifierConfigListPage.vue";
import ModifierConfigDetailPage from "@/components/Admin/Config/ModifierConfig/ModifierConfigDetailPage.vue";
import ModifierConditionListPage from "@/components/Admin/Config/ModifierCondition/ModifierConditionListPage.vue";
import ModifierConditionDetailPage from "@/components/Admin/Config/ModifierCondition/ModifierConditionDetailPage.vue";
import StatusConfigListPage from "@/components/Admin/Config/StatusConfig/StatusConfigListPage.vue";
import StatusConfigDetailPage from "@/components/Admin/Config/StatusConfig/StatusConfigDetailPage.vue";
import ActionCostListPage from "@/components/Admin/Config/ActionCost/ActionCostListPage.vue";
import ActionCostDetailPage from "@/components/Admin/Config/ActionCost/ActionCostDetailPage.vue";
import ActionConfigListPage from "@/components/Admin/Config/ActionConfig/ActionConfigListPage.vue";
import ActionConfigDetailPage from "@/components/Admin/Config/ActionConfig/ActionConfigDetailPage.vue";
import DaedalusConfigListPage from "@/components/Admin/Config/DaedalusConfig/DaedalusConfigListPage.vue";
import DaedalusConfigDetailPage from "@/components/Admin/Config/DaedalusConfig/DaedalusConfigDetailPage.vue";

export const adminConfigRoutes = [
    {
        name: "AdminGameConfigList",
        path: 'game-config-list',
        component: GameConfigListPage
    },
    {
        name: "AdminGameConfigDetail",
        path: 'game-config/:gameConfigId',
        component: GameConfigDetailPage
    },
    {
        name: "AdminModifierConfigList",
        path: 'modifier-config-list',
        component: ModifierConfigListPage
    },
    {
        name: "AdminModifierConfigDetail",
        path: 'modifier-config/:modifierConfigId',
        component: ModifierConfigDetailPage
    },
    {
        name: "AdminModifierConditionList",
        path: 'modifier-condition-list',
        component: ModifierConditionListPage
    },
    {
        name: "AdminModifierConditionDetail",
        path: 'modifier-condition/:modifierConditionId',
        component: ModifierConditionDetailPage
    },
    {
        name: "AdminStatusConfigList",
        path: 'status-config-list',
        component: StatusConfigListPage
    },
    {
        name: "AdminStatusConfigDetail",
        path: 'status-config/:statusConfigId',
        component: StatusConfigDetailPage
    },
    {
        name: "AdminActionCostList",
        path: 'action-cost-list',
        component: ActionCostListPage
    },
    {
        name: "AdminActionCostDetail",
        path: 'action-cost/:actionCostId',
        component: ActionCostDetailPage
    },
    {
        name: "AdminActionConfigList",
        path: 'action-config-list',
        component: ActionConfigListPage
    },
    {
        name: "AdminActionConfigDetail",
        path: 'action-config/:actionConfigId',
        component: ActionConfigDetailPage
    },
    {
        name: "AdminDaedalusConfigList",
        path: 'daedalus-config-list',
        component: DaedalusConfigListPage
    },
    {
        name: "AdminDaedalusConfigDetail",
        path: 'daedalus-config/:daedalusConfigId',
        component: DaedalusConfigDetailPage
    }
];
