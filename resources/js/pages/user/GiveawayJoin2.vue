<!-- <script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { useInitials } from '@/composables/useInitials';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { ref, computed, onMounted, onUnmounted, watchEffect } from 'vue';
import type { Ref } from 'vue';
import { AlarmClock } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { toast } from 'vue-sonner';
import moment from 'moment';
import confetti from "canvas-confetti";

const page = usePage();
const form = useForm({});

const props = defineProps<{
  giveaway: {
    id: number;
    skin_name: string;
    skin_image: string;
    value: string | number;
    rarity?: string;
    entries: number;
    participants: Array<{ id: number; name: string; entered_at?: string; ticketCount?: number }>;
    min_entries?: number;
    start_time?: string | null;
    duration_minutes?: number;
  },
  flash: {
   required: true,
  }
}>();

// reactive copy
const giveaway = ref({ ...props.giveaway }) as Ref<any>;

// participants state (clone so we can add _isNew flag)
const participants = ref<Array<any>>(giveaway.value.participants.map((p: any) => ({ ...p, _isNew: false })));
const winner = ref<any>(null);

// animated count
const displayed = ref<number>(giveaway.value.entries || 0);
const targetCount = ref<number>(giveaway.value.entries || 0);
const animFrame = ref<number | null>(null);
const isIncreasing = ref(false);

// timer + progress
const timeRemaining = ref<string>(giveaway.value.start_time ? '...' : 'Waiting');
const progress = ref<number>(giveaway.value.start_time ? 0 : 0);
let timerInterval: number | null = null;

// formatted start time
const formattedStart = computed(() => {
  if (!giveaway.value.start_time) return 'Not started';
  try {
    const d = new Date(giveaway.value.start_time);
    return d.toLocaleString();
  } catch {
    return giveaway.value.start_time;
  }
});

// progress color computed
const progressColor = computed(() => {
  if (progress.value >= 90) return 'bg-red-500';
  if (progress.value >= 60) return 'bg-amber-400';
  return 'bg-emerald-400';
});

// helper: initials
function initials(name: string) {
  if (!name) return 'U';
  return name.split(' ').map(s => s[0] ?? '').slice(0,2).join('').toUpperCase();
}

const getSkinTitle = (skinName: string) => {
  return skinName.split(' (')[0];
}

const getCondition = (skinName: string) => {
  const match = skinName.match(/\(([^)]+)\)/);
  return match ? match[1] : '';
}

const formatDate = (date: any) => {
  return moment(date).format('MMM D, YYYY')
};

// animate number function (tween)
function animateNumber(from: number, to: number, duration = 500) {
  const start = performance.now();
  if (animFrame.value) cancelAnimationFrame(animFrame.value);

  function tick(now: number) {
    const elapsed = now - start;
    const t = Math.min(1, elapsed / duration);
    // easeOutCubic
    const eased = 1 - Math.pow(1 - t, 3);
    displayed.value = Math.round(from + (to - from) * eased);
    if (t < 1) {
      animFrame.value = requestAnimationFrame(tick);
    } else {
      displayed.value = to;
      animFrame.value = null;
    }
  }

  animFrame.value = requestAnimationFrame(tick);
}


onMounted(() => {
  window.Echo.private(`giveaway.${props.giveaway.id}`).listen(".GiveawayUpdated", (event: any) => {

    console.log("START TIME RECEIVED:", event.giveaway.start_time);

    const old = targetCount.value;
    const newEntries = Number(event.giveaway.entries || 0);

    targetCount.value = newEntries;
    isIncreasing.value = newEntries > old;
    animateNumber(displayed.value, newEntries, 600);

    // update start_time
    
    giveaway.value.start_time = event.giveaway.start_time;
    startCountdown();

    if (event.giveaway.participants && Array.isArray(event.giveaway.participants)) {

        event.giveaway.participants.forEach((p: any) => {
            const exists = participants.value.some(pp => pp.id === p.id);

            if (!exists) {
                const newP = { ...p, _isNew: true };
                participants.value.unshift(newP);

                // remove highlight later
                setTimeout(() => (newP._isNew = false), 1500);
            }
        });
    }

    // WINNER
    if (event.giveaway.winner) {
        winner.value = event.giveaway.winner;

        confetti({
            particleCount: 200,
            spread: 70,
            origin: { y: 0.6 },
        });
    }

  });
});

function startCountdown() {
  if (timerInterval) clearInterval(timerInterval);

  const startTime = giveaway.value.start_time;
  const duration = giveaway.value.duration_minutes;

  // Missing or empty start
  if (!startTime || startTime === "0") {
    timeRemaining.value = "Waiting";
    progress.value = 0;
    return;
  }

  const start = new Date(startTime).getTime();
  const now = Date.now();

  // ❗Prevent weird backend values (start time older than 1 minute)
  if (now - start > 60 * 1000) {
    console.warn("Ignored invalid old start_time broadcast:", startTime);
    return;
  }

  const endTime = start + duration * 60 * 1000;

  timerInterval = window.setInterval(() => {
    const remaining = endTime - Date.now();

    if (remaining <= 0) {
      timeRemaining.value = "Finished";
      progress.value = 100;
      clearInterval(timerInterval!);
      return;
    }

    timeRemaining.value = formatTime(remaining);

    const total = duration * 60 * 1000;
    progress.value = Math.min(100, ((total - remaining) / total) * 100);
  }, 1000);
}

// format ms -> "Xd Xh Xm Xs"
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


const joinGiveaway = (id: number) => {
  //isJoining.value = true;
  form.post(route("giveaway.enter", id), {
    preserveScroll: true,
    onSuccess: () => {
      //isJoining.value = false;
      toast("Success", {
        description: page.props.flash.success,
      });
    },
    onError: () => {
      toast("Error", {
        description: form.errors.giveaway,
      });
    },
  });
};

const displayCount = computed(() => displayed.value);

onUnmounted(() => {
  if (animFrame.value) cancelAnimationFrame(animFrame.value);
  if (timerInterval) clearInterval(timerInterval);
});
</script> -->

<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import confetti from 'canvas-confetti';
import moment from 'moment';

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
    participants: Array<{ id: number; name: string; entered_at?: string; ticketCount?: number }>;
    min_entries?: number;
    start_time?: string | null;
    duration_minutes?: number;
  };
  entered?: boolean; // whether current user already entered (optional)
  flash?: any;
}>();

/* Reactive copies */
const giveaway = ref({ ...props.giveaway }) as any;
const participants = ref<Array<any>>( (giveaway.value.participants || []).map((p:any) => ({ ...p, _isNew: false })) );
const winner = ref<any | null>(null);

/* Animated entries */
const displayed = ref<number>(giveaway.value.entries || 0);
const targetCount = ref<number>(giveaway.value.entries || 0);
const isIncreasing = ref(false);
let animFrame: number | null = null;

/* Countdown/progress */
const timeRemaining = ref<string>(giveaway.value.start_time ? '...' : 'Waiting');
const progress = ref<number>(0);
let timerInterval: number | null = null;

/* Entered state from server - keep local too */
const entered = ref<boolean>(!!props.entered);

/* Keep the last validated start timestamp to avoid older broadcasts */
let lastStartMs: number | null = giveaway.value.start_time ? parseTime(giveaway.value.start_time) : null;


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

/* computed */
const displayCount = computed(() => displayed.value);
const formattedStart = computed(() => {
  if (!giveaway.value.start_time) return 'Not started';
  try { return new Date(giveaway.value.start_time).toLocaleString(); } catch { return String(giveaway.value.start_time); }
});
const progressColor = computed(() => {
  if (progress.value >= 90) return 'bg-red-500';
  if (progress.value >= 60) return 'bg-amber-400';
  return 'bg-emerald-400';
});

/* Animate numbers (tween) */
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

/* Countdown logic with strong guards */
function startCountdownIfValid() {
  // Clear any previous interval
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }

  if (!giveaway.value.start_time || !giveaway.value.duration_minutes) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  }

  const startMs = parseTime(giveaway.value.start_time);
  const durationMs = Number(giveaway.value.duration_minutes || 0) * 60 * 1000;
  if (!startMs || durationMs <= 0) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  }

  // Ignore broadcasts that are older than the last accepted start_time
  if (lastStartMs && startMs < lastStartMs) {
    // older -> ignore
    return;
  }

  // Accept this start time
  lastStartMs = startMs;

  const now = Date.now();

  // If start is in the future -> show waiting
  if (now < startMs) {
    timeRemaining.value = 'Waiting';
    progress.value = 0;
    return;
  }

  const endMs = startMs + durationMs;

  // If already expired, finish
  if (now >= endMs) {
    timeRemaining.value = 'Finished';
    progress.value = 100;
    return;
  }

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

/* Join (Inertia) */
function joinGiveaway(id:number) {
  form.post(route('giveaway.enter', id), {
    preserveScroll: true,
    onSuccess: (pageResponse:any) => {
      // backend likely broadcasts — UI will update from Echo
      entered.value = true;
      // if response contains updated start_time, ensure countdown starts too
      const st = pageResponse.props?.giveaway?.start_time ?? null;
      if (st) {
        giveaway.value.start_time = st;
        startCountdownIfValid();
      }
      if (page.props.flash?.success) {
        // show toast if available
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
   if (g.start_time !== null && g.start_time !== undefined) {

    const newStartMs = parseTime(g.start_time);

    // invalid or older -> ignore
    if (!newStartMs || (lastStartMs && newStartMs < lastStartMs)) {
      console.warn("Ignoring outdated start_time:", g.start_time);
    } 
    else {

      // ❗ DO NOT ACCEPT if duration_minutes is missing or zero
      if (!g.duration_minutes || g.duration_minutes < 1) {
        console.warn("Ignoring broadcast with invalid duration_minutes:", g.duration_minutes);
        return;
      }

      giveaway.value.start_time = g.start_time;
      giveaway.value.duration_minutes = g.duration_minutes; // update it safely
      lastStartMs = newStartMs;

      startCountdownIfValid(); // start only after full validation
    }

    } else {
    console.warn("Ignoring null start_time broadcast");
   }

    // --- participants list: add any new ones
    if (Array.isArray(g.participants)) {
      g.participants.forEach((p:any) => {
        const exists = participants.value.some(pp => pp.id === p.id);
        if (!exists) {
          const newP = { ...p, _isNew: true };
          participants.value.unshift(newP);
          setTimeout(() => (newP._isNew = false), 1500);
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

/* initial setup */
onMounted(() => {
  // set initial animations
  displayed.value = giveaway.value.entries || 0;
  targetCount.value = giveaway.value.entries || 0;
  // if giveaway already running, start countdown
  startCountdownIfValid();
  // subscribe to Echo after mount (if Echo exists)
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
  
   <div v-if="timeRemaining === 'Finished'" class="w-full max-w-6xl bg-black/40 backdrop-blur-md rounded-xl border border-red-500/20 mt-4 p-4 text-red-400 hover:border-red-500/40 transition-all duration-300 mb-4">
     <div class="flex items-center gap-3">
      <p class="text-sm font-medium">This giveaway has ended. You can no longer join.</p>
     </div>
   </div>

   <div v-if="props.flash.error" class="w-full max-w-6xl bg-black/40 backdrop-blur-md rounded-xl border border-red-500/20 mt-4 p-4 text-red-400 hover:border-red-500/40 transition-all duration-300 mb-4">
     <div class="flex items-center gap-3">
       <p class="text-sm font-medium">{{ props.flash.error }}</p>
     </div>
   </div>

   <div class="flex justify-center min-h-scree min-h-svh text-white p-6">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-[#1a2634] rounded-lg p- border border-[#2a3a4a] relative overflow-hidden">
          <div class="flex items-center justify-center mb-6 relative z-10">
            <img 
              :src="giveaway.image" 
              :alt="giveaway.skin_name"
              class="w-full max-w-xs object-contain"
            />
          </div>
          <div class="space-y-2 mx-3 mb-4">
            <!-- <p class="text-gray-400 text-sm uppercase tracking-wide">NOVA</p> -->
            <h2 class="text-2xl font-bold">
              <!-- StatTrak<sup class="text-sm">™</sup> Wood Fired -->
               <!-- {{ giveaway.skin_name }} -->
               {{ getSkinTitle(giveaway.skin_name) }}
            </h2>
            <p class="text-gray-400 text-sm">{{ getCondition(giveaway.skin_name) }}</p>
            <p class="text-green-400 text-3xl font-bold mt-4">$ {{giveaway.value}}</p>
          </div>
          <div class="absolute bottom-0 left-0 right-0 h-1" :style="{ backgroundColor: `#${giveaway.rarity}` }"></div>
        </div>

        <div class="space-y-4">
          <div class="bg-gradient-to-br from-[#2a3d2e] to-[#1a2d1e] rounded-lg p-6 border-2 border-green-700/50">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-start gap-4">
                <div class="bg-[#3a4d3e] p-3 rounded-lg">
                  <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-bold mb-2">Steam Level 5+</h3>
                  <p class="text-gray-300 text-sm">Meet the requirements to join: have Steam level 5. Good luck!</p>
                </div>
              </div>
              <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold whitespace-nowrap flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                YOU'RE IN!
              </button>
            </div>
          </div>

          <!-- <div class="bg-[#1a2634] rounded-lg p-6 border border-[#2a3a4a]">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="bg-[#3d3416] p-3 rounded-lg">
                  <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 100-4V6z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-gray-400 text-xs uppercase tracking-wide mb-1">YOUR TICKETS</p>
                  <p class="text-4xl font-bold">1</p>
                </div>
              </div>
              <button class="bg-[#2a3a4a] hover:bg-[#3a4a5a] p-3 rounded-full transition-colors">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
              </button>
            </div>
          </div> -->

          <div class="bg-[#1a2634] rounded-lg p-6 border border-[#2a3a4a]">
            <div class="grid grid-cols-2 gap-6">

              <div class="flex items-start gap-3">
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

              <div class="flex items-start gap-3">
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

              <div class="flex items-start gap-3">
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

              <div class="flex items-start gap-3">
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
        </div>
      </div>

      <div class="mt-8 bg-[#1a2634] rounded-lg p-6 border border-[#2a3a4a]">
        <div class="flex items-center gap-3 mb-6">
          <svg class="w-7 h-7 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
          </svg>
          <h3 class="text-2xl font-bold">Participants (9)</h3>
        </div>
        <div class="flex flex-wrap gap-3">
          <div v-for="i in 9" :key="i" class="w-14 h-14 rounded-lg bg-[#2a3a4a] overflow-hidden">
            <img 
              :src="`/placeholder.svg?height=56&width=56&query=gaming avatar ${i}`" 
              :alt="`Participant ${i}`"
              class="w-full h-full object-cover"
            />
          </div>
          <div v-for="i in 15" :key="`empty-${i}`" class="w-14 h-14 rounded-lg bg-[#0f1923] border border-[#2a3a4a]"></div>
        </div>
      </div>

    </div>

   </div>
   

  <!-- <div class="flex flex-col items-center justify-center min-h-svh">

    <div v-if="timeRemaining === 'Ended'" class="w-full max-w-6xl bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 mt-4 p-4 text-red-400 hover:border-orange-500/40 transition-all duration-300 mb-4">
      <div class="flex items-center gap-3">
        <p class="text-sm font-medium">This giveaway has ended. You can no longer join.</p>
      </div>
    </div>

    <div v-if="props.flash.error" class="w-full max-w-6xl bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 mt-4 p-4 text-red-400 hover:border-orange-500/40 transition-all duration-300 mb-4">
      <div class="flex items-center gap-3">
        <p class="text-sm font-medium">{{ props.flash.error }}</p>
      </div>
    </div>

   <div class="w-full max-w-6-xl mx-auto p-4 bg-slate-800 rounded-xl shadow-lg transition-all duration-300 text-white relative overflow-hidden">
    
    <div class="relative w-full h-52 rounded-lg mb-4 flex items-center justify-center bg-gradient-to-br from-black-90 to-black-20 border border-slate-700 overflow-hidden">
  
      <img :src="giveaway.image" 
        :alt="giveaway.skin_name"
        class="w-full h-full object-contain p-2 hover:scale-110 transition-transform duration-300">
    </div>



    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-bold">{{ giveaway.skin_name }}</h3>
        <p class="text-sm text-slate-300">{{ giveaway.rarity }} • ${{ giveaway.value }}</p>
      </div>

      <div class="flex flex-col items-center text-right">
        <div class="text-xs text-slate-400">Entries</div>
        <div class="text-2xl font-semibold flex items-center space-x-2">
          <span class="leading-none" aria-live="polite">{{ displayCount }}</span>
          <svg
            v-if="isIncreasing"
            class="w-5 h-5 text-emerald-400 animate-bounce"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>

    </div>

    <div class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm text-slate-300">
          <span v-if="timeRemaining === 'Waiting'">Waiting for min entries...</span>
          <span v-else-if="timeRemaining === 'Ended'">Ended</span>
          <span v-else>Ends in: {{ timeRemaining }}</span>
        </div>
        <div class="text-xs text-slate-400">{{ formattedStart }}</div>
      </div>

      <div class="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
        <div
          class="h-full rounded-full transform origin-left transition-all duration-1000 ease-out"
          :class="progressColor"
          :style="{ width: `${progress}%` }"
        ></div>
      </div>
    </div>
    
    <Button
      v-if="page.props.auth.user?.is_admin"
      variant="destructive"
      @click="simulateWinner(giveaway.id)"
    >
      🎯 Simulate Winner
    </Button>

    <Button @click="joinGiveaway(giveaway.id)" :disabled="giveaway.entered" class="flex justify-center w-full text-white py-3 mt-4 rounded-lg font-semibold transition-all duration-200" :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-slate-700 hover:bg-slate-900'">
      {{ giveaway.entered ? '✅ Joined' : '🎯 Join Giveaway' }}
    </Button>

    <div>
      <div class="text-xs uppercase text-slate-400 mt-5 mb-2">Participants</div>

       <transition-group name="list" tag="div" class="part-list old space-y-2">
        <div
          v-for="p in participants"
          :key="p.id + '-' + p.entered_at"
          class="flex items-center justify-between p-2 bg-slate-900/50 rounded-md border border-slate-700"
          :class="{ 'ring-2 ring-emerald-400': p._isNew }"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-semibold">
              {{ initials(p.name) }}
            </div>
            <div>
              <div class="text-sm font-medium">{{ p.name }}</div>
              <div class="text-xs text-slate-400">Ticket</div>
            </div>
          </div>

          <div class="text-sm text-slate-300">{{ p.ticketCount ?? '1' }}</div>
        </div>
      </transition-group>

      <transition-group name="participant-list" tag="div" class="space-y-2">

        <div v-for="p in participants"
          :key="p.id + '-' + p.entered_at"
          class="flex items-center justify-between p-2 bg-slate-900/50 rounded-md border border-slate-700 transform transition-all duration-500"
          :class="{'scale ring-2 ring-emerald-400': p._isNew}"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-semibold">
              {{ initials(p.name) }}
            </div>
            <div>
              <div class="text-sm font-medium">{{ p.name }}</div>
              <div class="text-xs text-slate-400">Ticket</div>
            </div>
          </div>
          <div class="text-sm text-slate-300">{{ p.ticketCount ?? '1' }}</div>
        </div>
      </transition-group>

      <div v-if="participants.length === 0" class="text-sm text-slate-400 italic py-4 text-center">
        No participants yet
      </div>
    </div>

     <div v-if="winner" class="winner-old absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-emerald-400 font-bold text-2xl rounded-xl animate-fade-in">
      🎉 Winner: {{ winner.name }} 🎉
    </div> 

    <div v-if="winner" class="mt-4 p-3 bg-emerald-500/20 rounded-lg text-center animate-winner-fade-in">
      🎉 Winner: <span class="font-bold">{{ winner.name }}</span> 🎉
    </div>

   </div>

  </div> -->

  </NewAppLayout>

</template>


<style scoped>


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