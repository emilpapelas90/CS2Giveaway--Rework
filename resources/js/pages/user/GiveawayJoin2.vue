<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import confetti from 'canvas-confetti';
import moment from 'moment';
import { Clock3 } from 'lucide-vue-next';

const page = usePage();
const form = useForm({});

const props = defineProps<{
  giveaway: {
    id: number;
    skin_name: string;
    skin_image?: string;
    value: string | number;
    rarity?: string;
    entries: number;
    participants: Array<{ id: number; name: string; }>;
    min_entries?: number;
    start_time?: string | null;
    duration_minutes?: number;
  };
  entered?: boolean; // whether current user already entered (optional)
  flash?: any;
}>();

/* Reactive copies */
const giveaway = reactive({ ...props.giveaway }) as any;
const participants = ref((giveaway.participants || []).map((p:any) => ({ ...p, _isNew: false })));

// const participants = ref((giveaway.participants || []).map((p:any) => ({ ...p, _isNew: false })).sort((a:any, b:any) => {
//   if(a.entered_at && b.entered_at) {
//     return new Date(a.entered_at).getTime() - new Date(b.entered_at).getTime();
//   }
//   return 0;
// }));
const winner = ref<any | null>(null);

/* Animated entries */
const displayed = ref<number>(giveaway.entries || 0);
const targetCount = ref<number>(giveaway.entries || 0);
const isIncreasing = ref(false);
let animFrame: number | null = null;

/* Countdown/progress */
const timeRemaining = ref<string>(giveaway.start_time ? '...' : 'Waiting');
const progress = ref<number>(0);
let timerInterval: number | null = null;

/* Entered state from server - keep local too */
const entered = ref<boolean>(!!props.entered);

/* Keep the last validated start timestamp to avoid older broadcasts */
let lastStartMs: number | null = giveaway.start_time ? parseTime(giveaway.start_time) : null;


function simulateWinner(id:number) {
  form.post(route('giveaway.simulate', id), {
    preserveScroll: true,

  });
}

// const simulateWinner = () => {
//   router.post(route('giveaway.simulate', giveaway.value.id), {}, {
//     preserveScroll: true,
//     onSuccess: () => {
//       toast('Simulation triggered');
//     },
//   });
// };

/* Helpers */
function initials(name: string) {
  if (!name) return 'U';
  return name.split(' ').map(s => s[0] ?? '').slice(0,2).join('').toUpperCase();
}
function getSkinTitle(name = '') { 
  return name.split(' (')[0]; 
}

const getCondition = (skinName: string) => {
  const match = skinName.match(/\(([^)]+)\)/);
  return match ? match[1] : '';
}

function formatTime(ms: number) {
  const totalSeconds = Math.max(0, Math.floor(ms / 1000));
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  if (days > 0) return `${days}d ${hours}h ${minutes}m ${seconds}s`;
  if (hours > 0) return `${hours}h ${minutes}m ${seconds}s`;
  if (minutes > 0) return `${minutes}m ${seconds}s`;
  return `${seconds}s`;
}

function parseTime(t: string | null | undefined) {
  if (!t) return null;
  // Accept both "2025-12-09 18:49:37" and ISO
  const ms = Date.parse(t);
  if (!isNaN(ms)) return ms;
  // try moment fallback
  const m = moment(t);
  return m.isValid() ? m.valueOf() : null;
}

const displayTime = computed(() => {
  switch (timeRemaining.value) {
    case "Waiting":
      return "Waiting for more participants...";
    case "Ended":
    case "Finished":
      return "Ended";
    default:
      return timeRemaining.value;
  }
});

const formattedStart = computed(() => {
  if (!giveaway.start_time) return "Not started";

  try {
    return new Date(giveaway.start_time).toLocaleString();
  } catch {
    return String(giveaway.start_time);
  }
});

const progressColor = computed(() => {
  if (progress.value >= 90) return 'bg-red-500';
  if (progress.value >= 60) return 'bg-amber-400';
  return 'bg-emerald-400';
});


const totalSlots = computed(() => Math.max(40, participants.value.length)); //ref(38);
const emptySlots = computed(() => {
  const filled = participants.value.length;
  const empty = Math.max(totalSlots.value - filled, 0);

  if (empty === 0) return [];

  const minOpacity = 0.10;
  const step = (1 - minOpacity) / (empty - 1 || 1);

  return Array.from({ length: empty }, (_v, i) => ({
    opacity: (1 - step * i).toFixed(2),
    index: i,
  }));
});

function animateNumber(from:number, to:number, duration = 500) {
  const start = performance.now();
  if (animFrame) cancelAnimationFrame(animFrame);
  function tick(now:number) {
    const elapsed = now - start;
    const t = Math.min(1, elapsed / duration);
    const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
    displayed.value = Math.round(from + (to - from) * eased);
    if (t < 1) animFrame = requestAnimationFrame(tick);
    else { displayed.value = to; animFrame = null; }
  }
  animFrame = requestAnimationFrame(tick);
}

function startCountdownIfValid() {
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }

  if (!giveaway.start_time || !giveaway.duration_minutes) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  }

  const startMs = parseTime(giveaway.start_time);
  const durationMs = Number(giveaway.duration_minutes || 0) * 60 * 1000;
  if (!startMs || durationMs <= 0) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  }

  // Ignore broadcasts that are older than the last accepted start_time
  if (lastStartMs && startMs < lastStartMs) {
    return;
  }

  // Accept this start time
  lastStartMs = startMs;

  const now = Date.now();
  const endMs = startMs + durationMs;

  // If start is in the future -> show waiting
  if (now < startMs) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  } else if (now >= endMs) {
    timeRemaining.value = 'Finished';
    progress.value = 100;
    return;
  } else {

  // Start ticking
  const tick = () => {
    const remaining = endMs - Date.now();
    if (remaining <= 0) {
      timeRemaining.value = 'Finished';
      progress.value = 100;
      if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
      return;
    }
    timeRemaining.value = formatTime(remaining);
    // progress relative to duration
    progress.value = Math.min(100, Math.max(0, Math.round(((durationMs - remaining) / durationMs) * 10000) / 100));
  };

  tick();
  timerInterval = window.setInterval(tick, 1000);
 }
}

/* Join (Inertia) */
function joinGiveaway(id:number) {
  form.post(route('giveaway.enter', id), {
    preserveScroll: true,
    onSuccess: (pageResponse:any) => {
      // backend likely broadcasts — UI will update from Echo
      entered.value = true;
      
      const updatedGiveaway = pageResponse.props?.giveaway;
      if (updatedGiveaway && typeof updatedGiveaway === 'object') {

        giveaway.entries = updatedGiveaway.entries ?? giveaway.entries;
        giveaway.participants = updatedGiveaway.participants ?? giveaway.participants;

        const old = displayed.value;
        const newEntries = Number(giveaway.entries);
        animateNumber(old, newEntries, 600);

        Object.assign(giveaway, updatedGiveaway); // merge instead of overwrite
        startCountdownIfValid();
      }

      if (page.props.flash?.success) {
        try { (window as any).toast?.success?.(page.props.flash.success); } catch {}
      }

    },
    onError: () => {
      // show error toast if available
      try { (window as any).toast?.error?.(form.errors.giveaway || 'Error'); } catch {}
    },
  });
}

function multiBurstConfetti() {
  for (let i = 0; i < 3; i++) {
    setTimeout(() => {
      confetti({
        particleCount: 150,
        spread: 90,
        origin: { x: Math.random(), y: 0.6 },
        colors: ['#10B981', '#FACC15', '#3B82F6', '#EF4444'],
      });
    }, i * 200);
  }
}

/* Echo listener */
let echoChannelName = `giveaway.${props.giveaway.id}`;

function subscribeEcho() {
  if (!(window as any).Echo) return; // not initialized yet
  const ch = (window as any).Echo.private(echoChannelName);

  ch.listen('.GiveawayUpdated', (payload: any) => {

    if (!payload?.giveaway) return;
    const g = payload.giveaway;

    // --- entries animation
    const old = targetCount.value;
    const newEntries = Number(g.entries || 0);
    targetCount.value = newEntries;
    isIncreasing.value = newEntries > old;
    animateNumber(displayed.value, newEntries, 600);

    // --- start time (validate) and restart countdown if valid



   //if (g.start_time !== null && g.start_time !== undefined) {

    //const newStartMs = parseTime(g.start_time);

    // if (!newStartMs || (lastStartMs && newStartMs < lastStartMs)) {
    //   console.warn("Ignoring outdated start_time:", g.start_time);
    // } 
    // else {

    //   // ❗ DO NOT ACCEPT if duration_minutes is missing or zero
    //   if (!g.duration_minutes || g.duration_minutes < 1) {
    //     console.warn("Ignoring broadcast with invalid duration_minutes:", g.duration_minutes);
    //     return;
    //   }

    //   giveaway.start_time = g.start_time;
    //   giveaway.duration_minutes = g.duration_minutes;
    //   lastStartMs = newStartMs;

    //   startCountdownIfValid();
    //}

    // } else {
    //  console.warn("Ignoring null start_time broadcast");
    // }

    // --- participants list: add any new ones
    if (Array.isArray(g.participants)) {
      g.participants.forEach((p:any) => {
          const index = participants.value.findIndex((pp:any) => pp.id == p.id);
        if (index === -1) {
          const newP = reactive({ ...p, _isNew: true });
          //participants.value.unshift(newP);
          participants.value.push(newP);
          setTimeout(() => (newP._isNew = false), 1500);
        } else {
          Object.assign(participants.value[index], p);
        }
      });
    }

  });

  ch.listen('.GiveawayEnded', (payload:any) => {
    const g = payload.giveaway;
    // --- winner reveal
    if (g.winner) {
      winner.value = g.winner;
      // confetti
      multiBurstConfetti();

      // confetti({
      //   particleCount: 300,
      //   spread: 120,
      //   origin: { y: 0.6 },
      //   gravity: 0.8,
      //   scalar: 1.2,
      //   colors: ['#10B981', '#FACC15', '#3B82F6', '#EF4444', '#F472B6'],
      //   ticks: 200,
      // });
    }
  })
}

// const participants = reactive([
//   { id: 1, name: 'Alice', _isNew: false },
//   { id: 2, name: 'Bob', _isNew: false },
//   { id: 3, name: 'Charlie', _isNew: false },
// ]);

// let nextId = 4;
// function addRandomParticipant() {
//   const names = ['David', 'Eve', 'Frank', 'Grace', 'Hannah', 'Ivy'];
//   const name = names[Math.floor(Math.random() * names.length)] + ' ' + nextId;
//   const newP = reactive({ id: nextId++, name, _isNew: true });
//   participants.unshift(newP);

//   setTimeout(() => { newP._isNew = false; }, 1500);
// }

/* initial setup */
onMounted(() => {
  displayed.value = giveaway.entries || 0;
  targetCount.value = giveaway.entries || 0;
  startCountdownIfValid();
  subscribeEcho();
});

/* cleanup */
onUnmounted(() => {
  if (animFrame) cancelAnimationFrame(animFrame);
  if (timerInterval) clearInterval(timerInterval);
  // leave channel
  try {
    if ((window as any).Echo) {
      (window as any).Echo.leave(echoChannelName);
    }
  } catch (e) {}
});
</script>

<template>
 <NewAppLayout>

    <!-- bg-[#0f1923] -->
  <div class="mx-6">
  
   <div v-if="displayTime === 'Ended'" class="w-full mx-auto max-w-7xl bg-black/40 backdrop-blur-md rounded-xl border border-red-500/20 p-3 text-red-400 hover:border-red-500/40 transition-all duration-300 mb-4">
     <div class="flex items-center justify-center gap-3">
      <p class="text-sm font-medium">This giveaway has ended. You can no longer join.</p>
     </div>
   </div>

   <div v-if="props.flash.error" class="w-full max-w-7xl bg-black/40 backdrop-blur-md rounded-xl border border-red-500/20 p-4 text-red-400 hover:border-red-500/40 transition-all duration-300 mb-4">
     <div class="flex items-center justify-center gap-3">
       <p class="text-sm font-medium">{{ props.flash.error }}</p>
     </div>
   </div>

   <div class="flex justify-center min-h-scree min-h-svh text-white">
    <div class="w-full max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
        <div class="flex flex-col col-span-2 py-4 bg-[#1a2634] rounded-lg p- border border-[#2a3a4a] relative overflow-hidden">
          <div class="flex items-center justify-center relative z-10">
            <img :src="giveaway.image" :alt="giveaway.skin_name"
              class="w-full h-44 max-w-xs object-contain hover:scale-110 transition-transform duration-300"/>
          </div>
          <div class="space-y-2 mx-4 mt-auto">
            <!-- <p class="text-gray-400 text-sm uppercase tracking-wide">NOVA</p> -->
            <h2 class="text-lg font-bold">
              <!-- StatTrak<sup class="text-sm">™</sup> Wood Fired -->
               <!-- {{ giveaway.skin_name }} -->
               {{ getSkinTitle(giveaway.skin_name) }}
            </h2>
            <p class="text-gray-400 text-sm">{{ getCondition(giveaway.skin_name) }}</p>
            <p class="text-green-400 text-md font-bold mt-2">$ {{giveaway.value}}</p>
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: `#${giveaway.rarity}` }"></div>
        </div>

        <div class="col-span-2 flex flex-col space-y-4">

          <div class="p- borde border-[#2a3a4a]">
            <div class="grid grid-cols-2 gap-3">

              <div class="flex items-start gap-3 bg-[#1a2634] rounded-lg p-3 border border-[#2a3a4a]">
                <div class="bg-[#3d3416] p-2 rounded-lg">
                  <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">ENDS IN</p>
                  <p class="text-white font-bold">Waiting for participants</p>
                </div>
              </div>

              <div class="flex items-start gap-3 bg-[#1a2634] rounded-lg p-3 border border-[#2a3a4a]">
                <div class="bg-[#3d3416] p-2 rounded-lg">
                  <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">PRIZE VALUE</p>
                  <p class="text-green-400 font-bold text-xl">$ 0.73</p>
                </div>
              </div>

              <div class="flex items-start gap-3 bg-[#1a2634] rounded-lg p-3 border border-[#2a3a4a]">
                <div class="bg-[#2a3a4a] p-2 rounded-lg">
                  <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">PARTICIPANTS</p>
                  <p class="text-white font-bold text-xl">9</p>
                </div>
              </div>

              <div class="flex items-start gap-3 bg-[#1a2634] rounded-lg p-3 border border-[#2a3a4a]">
                <div class="bg-[#3d3416] p-2 rounded-lg">
                  <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">STATUS</p>
                  <p class="text-white font-bold">Joined</p>
                </div>
              </div>
            </div>
          </div>


          <div class="mb-4 bg-[#1a2634] rounded-lg p-3 border border-[#2a3a4a]">
            <div class="flex items-center justify-between mb-2">
              <div class="text-xs text-slate-300">
                <div class="flex items-center justify-cente text-xs">
                  <div class="bg-[#0f1923] border border-[#2a3a4a] rounded-lg p-1 mr-2">
                   <Clock3 class="w-4 h-4"/>
                  </div>
                  <span class="mt-2 italic">{{ displayTime }}</span>
                </div>
              </div>
              <span class="text-xs italic mt-2 text-slate-400">{{ formattedStart }}</span>
            </div>

            <div class="w-full h-2.5 bg-[#0f1923] rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transform origin-left transition-all duration-1000 ease-out"
                :class="progressColor"
                :style="{ width: `${progress}%` }"
              ></div>
            </div>
          </div>

          <!-- <Button @click="joinGiveaway(giveaway.id)" :disabled="giveaway.entered" class="flex justify-center w-full text-white py-3 mt-4 rounded-lg font-semibold transition-all duration-200" :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-slate-700 hover:bg-slate-900'">
            {{ giveaway.entered ? '✅ Joined' : '🎯 Join Giveaway' }}
          </Button> -->

          <div class="mt-auto">
            <button @click="joinGiveaway(giveaway.id)" :disabled="giveaway.entered" class="bg-gradient-to-br from-[#2a3d2e] to-[#1a2d1e] border-2 border-green-700/50 text-white text-sm px-4 py-2 rounded-lg font-bold whitespace-nowrap w-full flex items-center justify-center gap-2 transition-colors"
             :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-slate-700 hover:bg-slate-900'">
                <svg v-if="giveaway.entered" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ giveaway.entered ? 'Joined' : 'Join the Giveaway' }}
                <!-- YOU'RE IN! -->
            </button>
          </div>

        </div>

      </div>


      {{ giveaway.participants }}
      {{ participants }}

      <div class="mt-8 bg-[#1a2634] rounded-lg p-6 border border-[#2a3a4a]">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-7 h-7 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
          </svg>
          <h3 class="text-2xl font-bold">Participants ({{ participants.length }})</h3>
        </div>
        <div class="flex flex-wrap gap-3">
          <div v-for="(p, i) in participants.slice().reverse()" :key="`p-${i}`"
          :class="['w-12 h-12 rounded-lg overflow-hidden', { 'animate-slideRight': p._isNew }]" 
          :style="{ animationDelay: (i * 50) + 'ms' }"
          class="bg-[#2a3a4a]" >
            <img :src="`https://ui-avatars.com/api/?name=${p.name}`" 
              :alt="`Participant ${p.name}`"
              class="w-full h-full object-cover"/>
          </div>

          <div v-for="(slot, i) in emptySlots" :key="`empty-${i}`" 
          class="p_list w-12 h-12 rounded-lg bg-[#0f1923] border border-[#2a3a4a] transition-all duration-300 hover:scale-105" 
          :style="{ opacity: slot.opacity, animationDelay: (slot.index * 38) + 'ms', }"></div>

        </div>
      </div>


      <button @click="addRandomParticipant" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
        Add Random Participant
      </button>

    </div>

   </div>

  </div>
 </NewAppLayout>
</template>

<style scoped>
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(6px) scale(0.95);
  }
  to {
    transform: translateY(0) scale(1);
  }
}

@keyframes slideRight {
  from {
    opacity: 0;
    transform: translateX(-10px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

.p_list {
  animation: fadeUp 0.4s ease both;
}

.animate-slideRight  {
  animation: slideRight 0.4s cubic-bezier(0.25, 1, 0.5, 1) both;
}

/* .fade-zoom-enter-active,
.fade-zoom-leave-active {
    transition: opacity 0.3s ease;
}
.fade-zoom-enter-from,
.fade-zoom-leave-to {
    opacity: 0;
} */

/* @keyframes popIn {
    0% {
        transform: scale(0.6);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-pop {
    animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
} */

/* @keyframes fade-in {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}
.animate-fade-in { animation: fade-in 0.35s ease-out forwards; }

.list-move { transition: transform .3s ease, opacity .25s ease; }

@keyframes winner-fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-winner-fade-in { animation: winner-fade-in 0.8s ease-out forwards; } */
</style>