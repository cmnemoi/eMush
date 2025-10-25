<template>
    <div class="achievement-wrapper">
        <div class="crown" />
        <div class="statistics-container">
            <!-- Header Section with different background -->
            <div class="header-section">
                <!-- Title Section - Full width header -->
                <div class="title-header">
                    <h2 class="title">{{ $t('achievements.title') }}</h2>
                </div>

                <!-- Points Section -->
                <div class="points-section">
                    <div class="points">{{ $t('achievements.points', { points: totalPoints }) }}</div>
                </div>

                <!-- Main Stats Cards -->
                <div class="stats-cards">
                    <Tippy
                        class="stat-card"
                        v-for="statistic in topThreeStatistics"
                        :key="statistic.name"
                        :class="{ active: activeCard === statistic.key }"
                        @click="setActiveCard(statistic.key)"
                    >
                        <div class="card-value">{{ statistic.count }}</div>
                        <div class="card-icon">
                            <img :src="StatisticRecords[statistic.key].icon" :alt="statistic.name" />
                        </div>
                        <div class="card-text" :class="{ rare: statistic.isRare }">{{ statistic.name }}</div>
                        <template #content>
                            <h1>{{ statistic.name }}</h1>
                            <p>{{ statistic.description }}</p>
                        </template>
                    </Tippy>
                </div>

                <!-- Navigation Tabs -->
                <div class="nav-tabs">
                    <button
                        class="tab-button"
                        :class="{ active: activeTab === 'stats' }"
                        @click="setActiveTab('stats')"
                    >
                        {{ $t('achievements.tabs.statistics') }}
                    </button>
                    <button
                        class="tab-button"
                        :class="{ active: activeTab === 'gains' }"
                        @click="setActiveTab('gains')"
                    >
                        {{ $t('achievements.tabs.gains', { count: achievementCount }) }}
                    </button>
                </div>
            </div>

            <!-- Statistics List -->
            <div class="stats-list" v-if="activeTab === 'stats'">
                <Tippy
                    class="stat-item"
                    v-for="statistic in statistics"
                    :key="statistic.name"
                >
                    <div class="stat-icon">
                        <img :src="StatisticRecords[statistic.key].icon" :alt="statistic.name" />
                    </div>
                    <div class="stat-name" :class="{ rare: statistic.isRare }">{{ statistic.name }}</div>
                    <div class="stat-value">{{ statistic.formattedCount }}</div>
                    <template #content>
                        <h1>{{ statistic.name }}</h1>
                        <p>{{ statistic.description }}</p>
                    </template>
                </Tippy>
            </div>

            <!-- Gains List -->
            <div class="stats-list" v-if="activeTab === 'gains'">
                <Tippy class="stat-item" v-for="achievement in achievements" :key="achievement.name">
                    <div class="stat-icon">
                        <img :src="StatisticRecords[achievement.statisticKey].icon" />
                    </div>
                    <div class="stat-name">{{ achievement.name }}</div>
                    <div class="stat-value">{{ achievement.formattedPoints }}</div>
                    <template #content>
                        <h1>{{ achievement.statisticName }}</h1>
                        <p>{{ achievement.statisticDescription }}</p>
                    </template>
                </Tippy>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onBeforeMount } from 'vue';
import { useStore } from 'vuex';
import { Tippy }  from 'vue-tippy';
import { getImgUrl } from '@/utils/getImgUrl';
import { Achievement, Statistic } from './models';
import { User } from '../userProfile/models';
import { useRoute } from 'vue-router';
import { StatisticRecords } from './enum';

const route = useRoute();
const store = useStore();

const props = defineProps<{ user: User }>();

// Store getters
const language = computed<string>(() => store.getters['locale/currentLocale']);
const statistics = computed<Statistic[]>(() => store.getters['achievements/statistics']);
const topThreeStatistics = computed<Statistic[]>(() => store.getters['achievements/topNStatistics'](3));
const totalPoints = computed<integer>(() => store.getters['achievements/points']);
const achievements = computed<Achievement[]>(() => store.getters['achievements/achievements']);
const achievementCount = computed<integer>(() => store.getters['achievements/achievements'].length);
const user = computed<User>(() => props.user);

// Store actions
async function fetchStatistics(payload: { userId: integer; language: string }) {
    await store.dispatch('achievements/fetchStatistics', payload);
}
async function fetchAchievements(payload: { userId: integer; language: string }) {
    await store.dispatch('achievements/fetchAchievements', payload);
}

function setActiveCard(card: string) {
    activeCard.value = card;
}
function setActiveTab(tab: string) {
    activeTab.value = tab;
}

// Reacting to component lifecycle
onBeforeMount(async () => {
    await Promise.allSettled([
        fetchStatistics({ userId: user.value.id, language: language.value }),
        fetchAchievements({ userId: user.value.id, language: language.value })
    ]);
});

watch(language, async (language: string) => {
    await Promise.allSettled([
        fetchStatistics({ userId: user.value.id, language: language }),
        fetchAchievements({ userId: user.value.id, language: language })
    ]);
});

watch(route, async () => {
    await Promise.allSettled([
        fetchStatistics({ userId: user.value.id, language: language.value }),
        fetchAchievements({ userId: user.value.id, language: language.value })
    ]);
});

// Component local data
const activeCard   = ref<string>('');
const activeTab    = ref<'stats'   | string>('stats');
</script>

<style scoped lang="scss">
@use "sass:color";

.achievement-wrapper {
  position: relative;
  padding-top: 15px;
  margin: 0 auto;
}

.crown {
  background-image: url('/src/assets/images/ugoals_crown.webp');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  width: 125px;
  height: 16px;
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
}

.statistics-container {
  width: 100%;
  background-color: #292C69;
  font-family: Arial, sans-serif;
  color: #ffffff;
  padding: 0;
  border: 2px solid #feb500;
  box-shadow: 0 0 8px rgba(243, 156, 18, 0.3);
}

.header-section {
  background-color: #292C69;
  padding: 20px;
  padding-bottom: 0;
  margin-bottom: 0;
  border-bottom: 2px solid #33388A;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.title-header {
  margin: -20px -20px 8px -20px;

  .title {
    margin: 0;
    font-size: 18px;
    color: #ffffff;
    background-color: #20224D;
    background-image: radial-gradient(circle at 1px 1px, #232344 1px, transparent 0);
    background-size: 8px 8px;
    padding: 8px 16px;
    border-radius: 0;
    width: 100%;
    box-sizing: border-box;
    text-align: center;

    // Effet de relief
    box-shadow:
      inset 0 1px 0 rgba(255, 255, 255, 0.05),
      inset 0 -1px 0 rgba(0, 0, 0, 0.1),
      0 2px 4px rgba(0, 0, 0, 0.1);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(0, 0, 0, 0.2);
  }
}

.points-section {
  text-align: center;
  margin-bottom: 20px;

  .points {
    font-size: 24px;
    font-weight: bold;
    color: #ffffff;
  }
}

.stats-cards {
  display: flex;
  flex-direction: row;
  gap: 8px;
  margin-bottom: 20px;

  .stat-card {
    flex: 1;
    background-color: #353A8E;
    border: 2px solid #F0B449;
    border-radius: 4px;
    padding: 20px 8px 10px 8px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s ease;
    position: relative;
    box-shadow:
      0 0 8px rgba(243, 156, 18, 0.3),
      inset 0 1px 0 rgba(255, 255, 255, 0.05);

    .card-value {
      position: absolute;
      top: -12px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #292C69;
      color: #F0B449;
      font-size: 14px;
      font-weight: bold;
      padding: 2px 8px;
      border: 1px solid #F0B449;
      border-radius: 2px;
      min-width: 20px;
    }

    .card-icon {
      font-size: 16px;
      margin-bottom: 4px;
      display: flex;
      justify-content: center;
      align-items: center;

      img {
        width: 16px;
        height: 16px;
        object-fit: contain;
      }
    }

    .card-text {
      font-size: 10px;
      font-weight: bold;
      color: white;
      line-height: 1.2;

      &.rare {
        color: #F0B449;
      }
    }
  }
}

.nav-tabs {
  display: flex;
  flex-direction: row;
  margin-bottom: 15px;
  border-bottom: 1px solid #7f8c8d;

  .tab-button {
    background: none;
    border: none;
    color: #95a5a6;
    font-size: 11px;
    font-weight: bold;
    padding: 8px 12px;
    cursor: pointer;
    transition: color 0.2s ease;

    &.active {
      color: #F0B449;
      border-bottom: 2px solid #F0B449;
    }

    &:hover:not(.active) {
      color: #ffffff;
    }
  }
}

.stats-list {
  max-height: 300px;
  overflow-y: auto;
  padding: 20px;
  padding-top: 0;
  background-color: #353A8E;

  .stat-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #3f5a70;
    cursor: pointer;

    &:last-child {
      border-bottom: none;
    }

    .stat-icon {
      width: 20px;
      text-align: center;
      margin-right: 8px;
      font-size: 12px;
    }

    .stat-name {
      flex: 1;
      font-size: 11px;
      line-height: 1.3;
      font-weight: bold;
    }

    .stat-value {
      font-size: 11px;
      color: #ffffff;
      font-weight: bold;
      margin-left: 8px;
    }
  }
}

// Scrollbar styling for webkit browsers
.stats-list::-webkit-scrollbar {
  width: 6px;
}

.stats-list::-webkit-scrollbar-track {
  background: #34495e;
}

.stats-list::-webkit-scrollbar-thumb {
  background: #7f8c8d;
  border-radius: 3px;
}

.stats-list::-webkit-scrollbar-thumb:hover {
  background: #95a5a6;
}

// Responsive design
@media (max-width: 480px) {
  .statistics-container {
    padding: 15px;
    max-width: 100%;
  }

  .stats-cards {
    .stat-card {
      padding: 8px 4px;

      .card-text {
        font-size: 9px;
      }
    }
  }

  .nav-tabs {
    .tab-button {
      padding: 6px 8px;
      font-size: 10px;
    }
  }
}
</style>


