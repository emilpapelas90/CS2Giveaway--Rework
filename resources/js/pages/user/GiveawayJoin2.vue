<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { router, Link, Head, useForm, usePage } from '@inertiajs/vue3';
import { useInitials } from '@/composables/useInitials';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { ref, computed, onMounted, onUnmounted, watchEffect } from 'vue';
import { Users, DollarSign, CheckIcon, AlarmClock, Mail } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { toast } from 'vue-sonner';
import moment from 'moment';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Giveaways',
    href: '/giveaways',
  },
  {
    title: 'Join',
    href: '/giveaway/join',
  },
];

const props = defineProps(['giveaway', 'requirements', 'flash']);
const page = usePage();
const { getInitials } = useInitials();

const giveaway = ref({...props.giveaway});
const interval = ref<number | null>(null);

const form = useForm({});

// Methods
const formatTime = (ms: number): string => {
  const totalSeconds = Math.max(0, Math.floor(ms / 1000));
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  if (days > 0) return `${days}d ${hours}h ${minutes}m ${seconds}s`;
  if (hours > 0) return `${hours}h ${minutes}m ${seconds}s`;
  if (minutes > 0) return `${minutes}m ${seconds}s`;
  return `${days}d ${hours}h ${minutes}m ${seconds}s`;
};

const updateTimer = () => {
  const now = Date.now();
  const startTime = new Date(giveaway.value.created_at).getTime();
  const endTime = new Date(giveaway.value.end_time).getTime();
  const totalDuration = endTime - startTime;
  const timeLeft = endTime - now;
  const progress = Math.min(100, Math.max(0, ((totalDuration - timeLeft) / totalDuration) * 100));

  //console.log('Progress:', giveaway.value.progress);
 // giveaway.value.progress = progress;
  //giveaway.value.timeRemaining = timeLeft <= 0 ? 'Ended' : formatTime(timeLeft);

  giveaway.value = {
    ...giveaway.value,
    progress,
    timeRemaining: timeLeft <= 0 ? 'Ended' : formatTime(timeLeft)
  };
};

const joinGiveaway = (id: any) => {
  form.post(route('giveaway.enter', id), {
    preserveScroll: true,
    onSuccess: () => {
      toast('Success', {
        description: page.props.flash.success,
      });
      router.visit(route('giveaway.join', id), {
        preserveScroll: true,
        preserveState: false,
      });
    },
    onError: () => {
      toast('Error', {
        description: form.errors.giveaway,
      });
    }
  })
}

const getSkinTitle = (skinName: string) => {
  return skinName.split(' (')[0];
}

const getCondition = (skinName: string) => {
  const match = skinName.match(/\(([^)]+)\)/);
  return match ? match[1] : '';
}

const hasEnded = computed(() => {
  return new Date(giveaway.value.end_time).getTime() <= Date.now();
});

const formatDate = (date: any) => {
  return moment(date).format('MMM D, YYYY')
};

onMounted(() => {
  updateTimer();
  interval.value = setInterval(updateTimer, 1000);
});

onUnmounted(() => {
  if (interval.value) clearInterval(interval.value);
});
</script>

<template>
 <NewAppLayout>


      <div v-if="hasEnded" class="w-7xl bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-4 text-red-400 hover:border-orange-500/40 transition-all duration-300 mb-4">
        <div class="flex items-center gap-3">
          
          <p class="text-sm font-medium">This giveaway has ended. You can no longer join.</p>
        </div>
      </div>

      <!-- Giveaways Grid -->
        <div class="w-7xl bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-4 hover:border-orange-500/40 transition-all duration-300">
          
          <!-- Skin Image -->

          <div class="relative w-full h-52 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg mb-4 flex items-center justify-center border border-blue-500/30 overflow-hidden">
            <!-- <div class="absolute top-3 left-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white px-2 rounded">
              Daily
            </div> -->
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
          <div class="flex justify-between items-center">
            <span class="flex text-orange-400 font-semibold"><AlarmClock class="w-5 h-5 mr-1"/> {{ giveaway.timeRemaining }}</span>
            <span class="text-gray-400 text-sm">Ends: {{ formatDate(giveaway.end_time) }}</span>
          </div>

          <!-- Requirements -->


          <ul class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-4 mt-4 space-y-3">
            <li
              v-for="(req, ind) in giveaway.requirements"
              :key="ind"
              class="flex items-center justify-between border-b border-orange-500/20 last:border-b-0 pb-3">
              <div class="flex items-center gap-3">
                <!-- Icon & Info -->
                <template v-if="req.type === 'discord'">
                  <div class="bg-[#5865F2] text-white rounded-full w-10 h-10 flex items-center justify-center">
                    <!-- Discord SVG icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-discord" viewBox="0 0 16 16">
                      <path d="M13.545 2.907a13.2 13.2 0 0 0-3.257-1.011.05.05 0 0 0-.052.025c-.141.25-.297.577-.406.833a12.2 12.2 0 0 0-3.658 0 8 8 0 0 0-.412-.833.05.05 0 0 0-.052-.025c-1.125.194-2.22.534-3.257 1.011a.04.04 0 0 0-.021.018C.356 6.024-.213 9.047.066 12.032q.003.022.021.037a13.3 13.3 0 0 0 3.995 2.02.05.05 0 0 0 .056-.019q.463-.63.818-1.329a.05.05 0 0 0-.01-.059l-.018-.011a9 9 0 0 1-1.248-.595.05.05 0 0 1-.02-.066l.015-.019q.127-.095.248-.195a.05.05 0 0 1 .051-.007c2.619 1.196 5.454 1.196 8.041 0a.05.05 0 0 1 .053.007q.121.1.248.195a.05.05 0 0 1-.004.085 8 8 0 0 1-1.249.594.05.05 0 0 0-.03.03.05.05 0 0 0 .003.041c.24.465.515.909.817 1.329a.05.05 0 0 0 .056.019 13.2 13.2 0 0 0 4.001-2.02.05.05 0 0 0 .021-.037c.334-3.451-.559-6.449-2.366-9.106a.03.03 0 0 0-.02-.019m-8.198 7.307c-.789 0-1.438-.724-1.438-1.612s.637-1.613 1.438-1.613c.807 0 1.45.73 1.438 1.613 0 .888-.637 1.612-1.438 1.612m5.316 0c-.788 0-1.438-.724-1.438-1.612s.637-1.613 1.438-1.613c.807 0 1.451.73 1.438 1.613 0 .888-.631 1.612-1.438 1.612"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm text-muted-foreground">{{ req.label }}</p>
                    <p class="text-base font-medium">{{ req.server_name || 'Unknown Server' }}</p>
                  </div>
                </template>

                <template v-else-if="req.type === 'email'">
                  <div class="bg-green-500 text-white rounded-full w-10 h-10 flex items-center justify-center">
                    <!-- Email icon -->
                    <Mail class="w-5 h-5"/>
                  </div>
                  <div>
                    <p class="text-sm text-muted-foreground">{{ req.label }}</p>
                  </div>
                </template>
              </div>

              <div>
                <template v-if="req.type === 'discord'">
                  <a
                    v-if="!req.in_server"
                    :href="req.invite"
                    target="_blank"
                    rel="noopener"
                    class="bg-[#5865F2] text-white px-4 py-1.5 text-sm font-medium rounded-md hover:bg-[#4752c4] transition"
                  >
                    Join
                  </a>
                  <span v-else class="text-green-500 ml-2 flex items-center gap-1">
                    <CheckIcon class="w-4 h-4 inline" /> Joined
                  </span>
                </template>

                <template v-else-if="req.type === 'email'">
                  <a
                    v-if="!req.confirmed"
                    href="/settings"
                    class="bg-blue-600 text-white px-4 py-1.5 text-sm font-medium rounded-md hover:bg-blue-700 transition"
                  >
                    Confirm Email
                  </a>
                  <span v-else class="text-green-500 ml-2 flex items-center gap-1">
                    <CheckIcon class="w-4 h-4 inline" /> Confirmed
                  </span>
                </template>
              </div>
            </li>
          </ul>

          <!-- Join Button -->

          <Button v-if="requirements.every((req: any) => {
            if (req.type === 'discord') return req.in_server; if (req.type === 'email') return req.confirmed; return false; })" 
            @click="joinGiveaway(giveaway.id)" :disabled="giveaway.entered" class="flex justify-center w-full text-white py-3 mt-4 rounded-lg font-semibold transition-all duration-200" :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600'">
             <!-- <template v-if="giveaway.entered">
              <CheckIcon class="w-4 h-4" /> Joined
             </template>
             <template v-else>
              Join Giveaway
             </template> -->
              {{ giveaway.entered ? '✅ Joined' : '🎯 Join Giveaway' }}
          </Button>
          
          <!-- <Link :href="route('giveaway.join', giveaway.id)" :class="giveaway.joined ? 'bg-green-600 cursor-not-allowed' : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600'"
                  class="flex justify-center w-full text-white py-3 rounded-lg font-semibold transition-all duration-200">
            {{ giveaway.joined ? '✅ Joined' : '🎯 Join Giveaway' }}
          </Link> -->
        </div>


        <div class="w-7xl bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-4 mt-6 hover:border-orange-500/40 transition-all duration-300">
          <h2 class="text-xl text-center">Total Participants {{ giveaway.participants.length }}</h2>
          <div class="pt-6">
            <TooltipProvider>
              <div class="flex flex-wrap gap-3">
                <Tooltip v-for="user in giveaway.participants" :key="user.id">
                  <TooltipTrigger as-child>
                    <Avatar class="h-16 w-16 overflow-hidden rounded-full">
                      <AvatarFallback class="rounded-lg text-black dark:text-white">{{ getInitials(user.name) }}</AvatarFallback>
                    </Avatar>
                  </TooltipTrigger>
                  <TooltipContent side="top">
                    <p>{{ user.name }}</p>
                  </TooltipContent>
                </Tooltip>
              </div>
            </TooltipProvider>
          </div>
        </div>
      

      <!-- <div class="text-center mt-12">
        <button class="bg-black/40 border border-orange-500/20 text-white px-8 py-3 rounded-lg hover:border-orange-500 transition-all duration-200">
          Load More Giveaways
        </button>
      </div> -->

 </NewAppLayout>
</template>

<!-- <template>
    <Head title="Giveaway Join" />

    <AppLayout :breadcrumbs="breadcrumbs">

      <div class="max-w-3xl mx-auto mt-10">
       <Card class="shadow-md">
        <CardHeader>
          <div class="flex items-center justify-between mb-2">
            <Badge v-if="giveaway.type == 0" variant="secondary">Daily</Badge>
            <Badge v-else-if="giveaway.type == 1" variant="secondary">Weekly</Badge>
            <Badge v-else variant="secondary">Monthly</Badge>
            <span class="text-xs text-muted-foreground" v-if="giveaway.timeRemaining !== 'ENDED'">Ends in: {{ giveaway.timeRemaining }}</span>
            <span v-else class="text-xs text-muted-foreground">ENDED</span>
          </div>
          <CardTitle>{{ giveaway.skin_name }}</CardTitle>
        </CardHeader>

        <CardContent>
          <img :src="giveaway.image" :alt="giveaway.title" class="rounded-md w-full object-cover mb-4" />

          <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
            <Badge variant="secondary"><DollarSign class="w-4 h-4 text-foreground" />
            <span class="font-medium text-foreground">{{ giveaway.value }}</span></Badge>
      
            <Badge variant="secondary"><Users class="w-4 h-4 text-foreground" />
            <span class="font-medium text-foreground">{{ giveaway.entries.toLocaleString() }} / {{ giveaway.max_entries.toLocaleString() }}</span></Badge>
          </div>

          <Progress :model-value="giveaway.progress" class="mb-2" />
        </CardContent>

        <CardFooter>
          <div class="w-full flex-col">
            
            <Card v-for="(req, ind) in giveaway.requirements" :key="ind" class="p-4">
              <CardContent class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="bg-[#5865F2] text-white rounded-full w-10 h-10 flex items-center justify-center">
                    <i class="fab fa-discord text-lg"></i>
                  </div>
                  <div>
                    <p class="text-sm text-muted-foreground">Join Discord Server</p>
                    <p class="text-base font-medium">{{ req.server_name || 'Unknown Server' }}</p>
                  </div>
                </div>

                <a v-if="!req.in_server" :href="req.invite" target="_blank" rel="noopener" class="bg-[#5865F2] text-white px-4 py-1.5 text-sm font-medium rounded-md hover:bg-[#4752c4] transition">
                  Join
                </a>

                <span v-if="req.in_server" class="text-green-500 ml-2">
                  <CheckIcon class="w-4 h-4 inline" /> Joined
                </span>
              </CardContent>
            </Card>
    
            <Button v-if="requirements.every(req => req.in_server)" @click="joinGiveaway(giveaway.id)" :disabled="giveaway.entered" class="w-full mt-4">
             <template v-if="giveaway.entered">
              <CheckIcon class="w-4 h-4" /> Joined
             </template>
             <template v-else>
              Join Giveaway
             </template>
            </Button>
          </div>
        </CardFooter>
       </Card>
      </div>


      <Card class="max-w-xl mx-auto mt-6">
        <CardHeader>
          <CardTitle>Participants</CardTitle>
        </CardHeader>
        <CardContent>
          <TooltipProvider>
            <div class="flex flex-wrap gap-3">
              <Tooltip v-for="i in participants" :key="i.id">
                <TooltipTrigger as-child>
                  <Avatar class="h-12 w-12 overflow-hidden rounded-lg">
                    <AvatarImage :src="i.avatar!" :alt="i.name" />
                    <AvatarFallback class="rounded-lg text-black dark:text-white">
                        {{ getInitials(i.name) }}
                    </AvatarFallback>
                </Avatar>
                </TooltipTrigger>
                <TooltipContent side="top">
                  <p>{{ i.name }}</p>
                </TooltipContent>
              </Tooltip>
            </div>
          </TooltipProvider>
        </CardContent>
      </Card>


      <Card class="max-w-xl mx-auto mt-6">
        <CardHeader>
          <CardTitle>Participants</CardTitle>
        </CardHeader>
        <CardContent>
          <TooltipProvider>
            <div class="flex flex-wrap gap-3">
              <Tooltip v-for="user in giveaway.participants" :key="user.id">
                <TooltipTrigger as-child>
                  <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
                    <AvatarFallback class="rounded-lg text-black dark:text-white">{{ getInitials(user.name) }}</AvatarFallback>
                  </Avatar>
                </TooltipTrigger>
                <TooltipContent side="top">
                  <p>{{ user.name }}</p>
                </TooltipContent>
              </Tooltip>
            </div>
          </TooltipProvider>
        </CardContent>
      </Card>


       <Card class="max-w-xl mx-auto mt-6">
        <CardHeader>
            <CardTitle>Participants</CardTitle>
        </CardHeader>
        <CardContent>
          <ul class="text-sm text-muted-foreground space-y-1">
            <li v-for="user in giveaway.participants" :key="user.id">
                {{ user.name }}
            </li>
          </ul>
        </CardContent>
      </Card>
     <Toaster/>
    </AppLayout>
</template> -->

