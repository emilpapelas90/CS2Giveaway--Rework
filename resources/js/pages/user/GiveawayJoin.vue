<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
import { Users, DollarSign, CheckIcon } from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { toast } from 'vue-sonner';
import axios from 'axios';

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

  //if (days > 0) return `${days}d ${hours}h`
  //if (hours > 0) return `${hours}h ${minutes}m`
  return `${days}d ${hours}h ${minutes}m ${seconds}s`;
};

const updateTimer = () => {
  const now = Date.now();
  const startTime = new Date(giveaway.value.created_at).getTime();
  const endTime = new Date(giveaway.value.end_time).getTime();
  const totalDuration = endTime - startTime || 1;
  const timeLeft = endTime - now;
  const progress = Math.min(100, Math.max(0, ((now - startTime) / totalDuration) * 100));

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
 
// watchEffect(() => {
//   if (page.props.flash.success) {
//     toast('Success', {
//       description: page.props.flash.success,
//     });
//   }

//   if (page.props.flash.error) {
//     toast('Error', {
//       description: page.props.flash.error,
//     });
//   }
// });

//   if (hasJoined.value || giveaway.timeRemaining === 'Ended') return;

//   try {
//     await axios.post(`/giveaways/${giveaway.id}/join`);
//     hasJoined.value = true;
//     toast.success('Successfully joined the giveaway!');
//     // optionally update entries count
//     giveaway.total_entries += 1;
//   } catch (e) {
//     toast.error('Something went wrong.');
//   }
// };

// const shareReferral = () => {
//   const url = `${window.location.origin}/giveaways/${giveaway.id}?ref=${user.id}`;
//   navigator.clipboard.writeText(url).then(() => {
//     toast.success('Referral link copied!');
//   });
// };

onMounted(() => {
  updateTimer();
  interval.value = setInterval(updateTimer, 1000);
});

onUnmounted(() => {
  if (interval.value) clearInterval(interval.value);
});


const participants = [
  {
    id: 1,
    name: 'Alice Johnson',
    avatar: 'https://i.pravatar.cc/100?img=1'
  },
  {
    id: 2,
    name: 'Bob Smith',
    avatar: 'https://i.pravatar.cc/100?img=2'
  },
  {
    id: 3,
    name: 'Charlie Kim',
    avatar: 'https://i.pravatar.cc/100?img=3'
  },
  {
    id: 4,
    name: 'Dana Lee',
    avatar: 'https://i.pravatar.cc/100?img=4'
  },
  {
    id: 5,
    name: 'Evan White',
    avatar: 'https://i.pravatar.cc/100?img=5'
  },
  {
    id: 6,
    name: 'Fiona Grey',
    avatar: 'https://i.pravatar.cc/100?img=6'
  },
  {
    id: 7,
    name: 'George Stone',
    avatar: 'https://i.pravatar.cc/100?img=7'
  },
  {
    id: 8,
    name: 'Hannah Gold',
    avatar: 'https://i.pravatar.cc/100?img=8'
  },
]

</script>

<template>
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
                    <i class="fab fa-discord text-lg"></i> <!-- You can replace with an SVG icon too -->
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


      <!-- <Card class="max-w-xl mx-auto mt-6">
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
      </Card> -->


      <!-- <Card class="max-w-xl mx-auto mt-6">
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
      </Card> -->
     <Toaster/>
    </AppLayout>
</template>

