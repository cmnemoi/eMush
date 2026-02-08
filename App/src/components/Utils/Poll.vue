<template>
    <div class="poll-container">
        <div class="poll-title">{{poll.title}}</div>
        <div
            v-for="option in poll.options"
            :key="option.id"
            class="vote-option"
            :class="{ selected: selectedOptions.indexOf(option.id) !== -1, voted: option.voted }"
            @click.stop="selectOption(option.id)"
        >
            <div class="progress-bar" :style="{ width: getPercentage(option.id) + '%' }"></div>
            <div class="vote-content">
                <div class="vote-icon"></div>
                <div class="vote-text">{{ option.name }}</div>
                <div class="vote-stats" v-if="!poll.canVote">
                    <span class="vote-count">{{ getPercentage(option.id).toFixed(2) + '% (' + option.votes + ')'}}</span>
                </div>
            </div>
        </div>
        <div class="poll-title"></div>

        <button
            v-if="poll.canVote"
            class="vote-button"
            :disabled="selectedOptions.length === 0"
            @click.stop="submitVote">
            {{$t('poll.vote') + ' (' + votesRemainings + ')' }}
        </button>
        <button
            v-if="poll.voted"
            class="vote-button"
            @click.stop="removeVotes">
            {{ $t('poll.cancel') }}
        </button>

        <div v-if="poll.canVote === false && poll.voted === false"> {{ $t('poll.canNotVote') }}</div>

        <button
            v-if="isAdmin && poll.closed === false"
            class="vote-button"
            @click.stop="closePoll">
            {{ $t('poll.close') }}
        </button>
    </div>
</template>

<script lang="ts">
import { Poll } from '@/entities/Poll';
import { PollOption } from '@/entities/PollOption';
import UserService from '@/services/user.service';
import { defineComponent } from 'vue';
import { mapGetters } from "vuex";

export default defineComponent({
    name: 'Poll',
    props : {
        poll: {
            type: Poll,
            required: true
        }
    },
    data() {
        return {
            selectedOptions : [] as Array<number>,
            votesRemainings : this.poll.remainingVotes

        };
    },
    computed: {
        ...mapGetters('auth', ['isAdmin'])
    },
    methods: {
        selectOption(optionId: number) {

            if (this.poll.canVote === false || this.poll.options.find((o: PollOption) => o.id === optionId)?.voted)
            {
                return;
            }

            const index = this.selectedOptions.indexOf(optionId);
            if (index === -1)
            {


                if(this.votesRemainings < 1)
                {
                    this.selectedOptions.splice(0,1);
                    this.votesRemainings += 1;
                }
                this.selectedOptions.push(optionId);
                this.votesRemainings -= 1;
            }
            else {
                this.selectedOptions.splice(index,1);
                this.votesRemainings +=1;
            }

        },
        getPercentage(optionId: number) {
            const n = (this.poll.options.find(o => o.id === optionId)?.votes ?? 0) / this.poll.voteCount * 100;
            return isNaN(n) ? 0 : n;
        },
        async submitVote() {
            UserService.voteInPoll(this.poll.id, this.selectedOptions)
                .then((result) => {
                    this.poll.load(result);
                    this.selectedOptions = [];
                });
        },
        async removeVotes() {
            UserService.removeVotesInPoll(this.poll.id)
                .then((result) => {
                    this.poll.load(result);
                    this.selectedOptions = [];
                    this.votesRemainings = this.poll.remainingVotes;

                });
        },
        async closePoll() {
            UserService.closePoll(this.poll.id)
                .then((result) => {
                    this.poll.load(result);
                    this.selectedOptions = [];

                });
        }
    }
});
</script>

<style scoped lang="scss">
.poll-container {
	background: rgba(0, 0, 0, 0.2);
	border-radius: 12px;
	padding: 20px;
	border: 1px solid rgba(255, 215, 0, 0.2);
}

.poll-title {
	color: rgb(210, 71, 129);
	font-size: 1.1rem;
	font-weight: 600;
	margin-bottom: 15px;
	text-align: center;
}

.vote-option {
	position: relative;
	margin: 12px 0;
	cursor: pointer;
	transition: all 0.3s ease;
	background: linear-gradient(135deg, rgba(210, 71, 129, 0.1), rgba(210, 71, 129, 0.05));
	border: 2px solid rgba(210, 71, 129, 0.3);
	border-radius: 10px;
	padding: 16px 20px;
	backdrop-filter: blur(10px);
	overflow: hidden;
}

.vote-option:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 25px rgba(210, 71, 129, 0.2);
	border-color: rgba(210, 71, 129, 0.6);
}

.vote-option.selected {
	background: linear-gradient(135deg, rgba(210, 71, 129, 0.2), rgba(210, 71, 129, 0.1));
	border-color: rgba(210, 71, 129);
	box-shadow: 0 0 20px rgba(210, 71, 129, 0.3);
}

.vote-option.voted {
	cursor: default;
	background: linear-gradient(135deg, rgba(210, 71, 129, 0.3), rgba(210, 71, 129, 0.15));
}

.vote-content {
	display: flex;
    flex-direction: row;
	align-items: center;
	justify-content: space-between;
	position: relative;
	z-index: 2;
}

.vote-text {
	color: #debcf4;
	font-weight: 500;
	font-size: 1rem;
	flex: 1;
}

.vote-stats {
	display: flex;
	align-items: center;
	gap: 10px;
	min-width: 80px;
	justify-content: flex-end;
}

.vote-count {
	color: rgb(210, 71, 129);
	font-weight: bold;
	font-size: 0.9rem;
}

.vote-percentage {
	color: rgb(210, 71, 129);
	font-size: 0.85rem;
	font-weight: 500;
}

.progress-bar {
	position: absolute;
	left: 0;
	top: 0;
	height: 100%;
	background: linear-gradient(90deg, rgba(210, 71, 129, 0.15), rgba(210, 71, 129, 0.05));
	border-radius: 8px;
	transition: width 0.8s ease-in-out;
	width: 0%;
}

.vote-option.selected .progress-bar {
	background: linear-gradient(90deg, rgba(210, 71, 129, 0.25), rgba(210, 71, 129, 0.1));
}

.vote-icon {
	width: 20px;
	height: 20px;
	border-radius: 50%;
	border: 2px solid rgba(210, 71, 129, 0.5);
	margin-right: 15px;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.3s ease;
	flex-shrink: 0;
}

.vote-option.selected .vote-icon {
	background: rgba(210, 71, 129, 0.5);;
	border-color: rgba(210, 71, 129, 0.5);;
}

.vote-icon::after {
	content: '✓';
	color: #2b0826;
	font-weight: bold;
	font-size: 12px;
	opacity: 0;
	transition: opacity 0.3s ease;
}

.vote-option.selected .vote-icon::after {
	opacity: 1;
}

.total-votes {
	text-align: center;
	margin-top: 20px;
	padding-top: 15px;
	border-top: 1px solid rgba(255, 215, 0, 0.2);
	color: #d2b48c;
	font-size: 0.9rem;
}

.vote-button {
	width: 100%;
	padding: 12px 20px;
	background: linear-gradient(135deg, rgb(160, 54, 98), rgb(139, 46, 85));
	border: none;
	border-radius: 8px;
	color: #debcf4;
	font-weight: bold;
	font-size: 1rem;
	cursor: pointer;
	transition: all 0.3s ease;
	margin-top: 15px;
	box-shadow: 0 4px 15px rgba(210, 71, 129, 0.3);;
}

.vote-button:hover {
	transform: translateY(-1px);
	box-shadow: 0 6px 15px rgba(210, 71, 129, 0.4);;
}

.vote-button:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
}

.counter {
	display: flex;
	align-items: center;
	gap: 8px;
	color: #d2b48c;
	text-decoration: none;
	padding: 8px 12px;
	border-radius: 20px;
	background: rgba(0, 0, 0, 0.2);
	transition: all 0.3s ease;
}

.counter:hover {
	background: rgba(255, 215, 0, 0.1);
}

.counter img {
	width: 16px;
	height: 16px;
	filter: brightness(0.8);
}
</style>
