<template>
 <NewAppLayout>

  <div class="">

      <div class="w-full max-w-7xl mx-auto mb-8 text-left">
        <h1 class="text-4xl font-bold text-white mb-4">Active Raffles</h1>
        <p class="text-gray-300 text-lg">{{ giveaways.length }} active raffles worth of: ${{ totalValue.toFixed(2) }}</p>
      </div>

      <!-- Filters -->
      <div class="mb-8 flex flex-wrap gap-4">
        <select v-model="selectedFilter" class="bg-black/40 border border-orange-500/20 text-white px-4 py-2 rounded-lg focus:outline-none focus:border-orange-500">
          <option value="all">All Giveaways</option>
          <option value="high-value">High Value</option>
          <option value="ending-soon">Ending Soon</option>
          <option value="new">New</option>
        </select>
        <select v-model="sortBy" class="bg-black/40 border border-orange-500/20 text-white px-4 py-2 rounded-lg focus:outline-none focus:border-orange-500">
          <option value="newest">Newest First</option>
          <option value="value-high">Highest Value</option>
          <option value="value-low">Lowest Value</option>
          <option value="ending-soon">Ending Soon</option>
        </select>
      </div>

    </div>

      <!-- Giveaways Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(giveaway, i) in filteredGiveaways" :key="i"
             class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6 hover:border-orange-500/40 transition-all duration-300">
          
          <!-- Skin Image -->

          <div class="relative w-full h-52 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg mb-4 flex items-center justify-center border border-blue-500/30 overflow-hidden">
            <div class="absolute top-3 left-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white px-2 rounded">
              Daily
            </div>

            <img :src="giveaway.image" 
                :alt="giveaway.skin_name"
                class="w-full h-full object-contain p-2 hover:scale-110 transition-transform duration-300">
          </div>

          <!-- Skin Info -->
          <h3 class="text-xl font-bold text-white mb-2">{{ getSkinTitle(giveaway.skin_name) }}</h3>
          <p class="text-gray-400 mb-4">{{ getCondition(giveaway.skin_name) }}</p>
          
          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="flex justify-between text-gray-400 mb-1">
              <span class="text-orange-400 font-bold">$ {{ giveaway.value }}</span>
              <span class="text-sm">{{ giveaway.entries.toLocaleString() }} / {{ giveaway.max_entries.toLocaleString() }} Entries</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
              <div class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300"
                   :style="{ width: giveaway.progress + '%' }"></div>
            </div>
          </div>

          <!-- Time Left -->
          <div class="flex justify-between items-center mb-4">
            <span class="text-orange-400 font-semibold">⏰ {{ giveaway.timeRemaining }}</span>
            <span class="text-gray-400 text-sm">Ends: {{ formatDate(giveaway.end_time) }}</span>
          </div>

          <!-- Join Button -->
          
          <Link :href="route('giveaway.join', giveaway.id)" :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600'"
                  class="flex justify-center w-full text-white py-3 rounded-lg font-semibold transition-all duration-200">
            {{ giveaway.joined ? '✅ Joined' : '🎯 Join Giveaway' }}
          </Link>
        </div>
      </div>

      <!-- <div class="text-center mt-12">
        <button class="bg-black/40 border border-orange-500/20 text-white px-8 py-3 rounded-lg hover:border-orange-500 transition-all duration-200">
          Load More Giveaways
        </button>
      </div> -->

  </NewAppLayout>
</template>


<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue'
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Users, DollarSign,  } from 'lucide-vue-next';
import moment from 'moment';

const props = defineProps(['giveaways']);

const giveaways = ref([...props.giveaways]);

const selectedFilter = ref('all');
const sortBy = ref('newest');
const interval = ref<number | null>(null);


//Computed properties
const totalValue = computed(() => {
  return giveaways.value.reduce((sum, giveaway) => sum + Number(giveaway.value || 0), 0)
})

const filteredGiveaways = computed(() => {
  let filtered = [...giveaways.value]

  // Apply filters
  if (selectedFilter.value === 'high-value') {
    filtered = filtered.filter(g => g.value >= 100)
  } else if (selectedFilter.value === 'ending-soon') {
    filtered = filtered.filter(g => g.progress > 70)
  } else if (selectedFilter.value === 'new') {
    filtered = filtered.filter(g => g.progress < 30)
  }

  // Apply sorting
  if (sortBy.value === 'value-high') {
    filtered.sort((a, b) => b.value - a.value)
  } else if (sortBy.value === 'value-low') {
    filtered.sort((a, b) => a.value - b.value)
  } else if (sortBy.value === 'ending-soon') {
    filtered.sort((a, b) => b.progress - a.progress)
  }

  return filtered
})

// Methods

const formatTime = (ms: number): string => {
  const totalSeconds = Math.max(0, Math.floor(ms / 1000));
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  //if (days > 0) return `${days}d ${hours}h ${minutes}m ${seconds}s`;
  //if (hours > 0) return `${hours}h ${minutes}m ${seconds}s`;
  return `${days}d ${hours}h ${minutes}m ${seconds}s`;
};

const updateTimers = () => {
  const now = Date.now();

  giveaways.value = giveaways.value.map(giveaway => {

    const startTime = new Date(giveaway.created_at).getTime();
    const endTime = new Date(giveaway.end_time).getTime();

    const totalDuration = endTime - startTime || 1;
    const elapsed = now - startTime;
    const timeLeft = endTime - now;

    const progress = Math.min(100, Math.max(0, ((now - startTime) / totalDuration) * 100));

    return {
      ...giveaway,
      timeRemaining: timeLeft <= 0 ? 'Ended' : formatTime(timeLeft),
      progress
    };
  });
};

const getSkinTitle = (skinName) => {
  return skinName.split(' (')[0]; // removes everything after ' ('
}

const getCondition = (skinName) => {
  const match = skinName.match(/\(([^)]+)\)/); // matches the part inside parentheses
  return match ? match[1] : ''; // return 'Field-Tested' or ''
}

const formatDate = (date: any) => {
  //return new Date(date).toLocaleString();
  //return moment(date).format('MMMM D, YYYY h:mm A')
  return moment(date).format('MMM D, YYYY')
};

onMounted(() => {
  updateTimers();
  interval.value = setInterval(updateTimers, 1000);
});

onUnmounted(() => {
  if (interval.value) {
    clearInterval(interval.value);
  }
});

const winners = [
  { winner: "Emil90 Skinrave.gg", prize: "$3.84" },
  { winner: "Unknown", prize: "$5.08" },
  { winner: "Unknown", prize: "$3.74" },
  { winner: "Unknown", prize: "$0.38" },
  { winner: "Unknown", prize: "$2.50" },
]

</script>