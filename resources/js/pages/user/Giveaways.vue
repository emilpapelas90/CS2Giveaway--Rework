<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Link, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Users, DollarSign,  } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Giveaways',
    href: '/giveaways',
  },
];

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

<template>
    <Head title="Giveaways" />

    <AppLayout :breadcrumbs="breadcrumbs">

     <div class="px-4 py-8">

        <div class="mb-8">
        <h2 class="text-3xl font-bold mb-2 main-title">Active Giveaways</h2>
        <p class="text-muted-foreground">{{ giveaways.length }} active giveaways • Total value: ${{ totalValue.toFixed(2) }}</p>
      </div>

      
      <div class="mb-8 flex flex-wrap gap-4">
        <Select v-model="selectedFilter">
         <SelectTrigger class="w-[180px]">
          <SelectValue placeholder="Select a fruit" />
         </SelectTrigger>
         <SelectContent>
          <SelectGroup>
            <SelectItem value="all">All Giveaways</SelectItem>
            <SelectItem value="high-value">High Value</SelectItem>
            <SelectItem value="ending-soon">Ending Soon</SelectItem>
            <SelectItem value="new">New</SelectItem>
          </SelectGroup>
         </SelectContent>
        </Select>

        <Select v-model="sortBy">
         <SelectTrigger class="w-[180px]">
          <SelectValue placeholder="Select a fruit" />
         </SelectTrigger>
         <SelectContent>
          <SelectGroup>
            <SelectItem value="newest">Newest First</SelectItem>
            <SelectItem value="value-high">Highest Value</SelectItem>
            <SelectItem value="value-low">Lowest Value</SelectItem>
            <SelectItem value="ending-soon">Ending Soon</SelectItem>
          </SelectGroup>
         </SelectContent>
        </Select>
      </div>
      
      <!-- Giveaways Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">

        <Card v-for="(giveaway, i) in filteredGiveaways" :key="i" class="hover:shadow-lg transition-shadow flex flex-col justify-between">
          <CardHeader>
            <div class="flex items-center justify-between mb-2">
              <Badge v-if="giveaway.type == 0" variant="secondary">Daily</Badge>
              <Badge v-else-if="giveaway.type == 1" variant="secondary">Weekly</Badge>
              <Badge v-else variant="secondary">Monthly</Badge>
              <span class="text-xs text-muted-foreground" v-if="giveaway.timeRemaining !== 'ENDED'">Ends in: {{ giveaway.timeRemaining }}</span>
              <span v-else class="text-xs text-muted-foreground">ENDED</span>
            </div>
            <CardTitle>{{ giveaway.skin_name }}</CardTitle>
            <p class="text-sm text-muted-foreground">{{ giveaway.description }}</p>
          </CardHeader>

          <CardContent>
            <img
              :src="giveaway.image"
              :alt="giveaway.title"
              class="rounded-md w-full h-40 object-cover mb-4"
            />

            <!-- Price & Entries -->

            <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
              <Badge variant="secondary"><DollarSign class="w-4 h-4 text-foreground" />
              <span class="font-medium text-foreground">{{ giveaway.value }}</span></Badge>
      
              <Badge variant="secondary"><Users class="w-4 h-4 text-foreground" />
              <span class="font-medium text-foreground">{{ giveaway.entries.toLocaleString() }} / {{ giveaway.max_entries.toLocaleString() }}</span></Badge>
            </div>
        

            <!-- Progress bar -->
            <div class="mb-2">
              <Progress v-model="giveaway.progress" class="w-5/5"></Progress>
            </div>
          </CardContent>

          <CardFooter>
            <Link :href="route('giveaway.join', giveaway.id)" class="w-full"><Button variant="outline" class="w-full">Join Giveaway</Button></Link>
          </CardFooter>
        </Card>

      </div>

      <!-- Winners Section -->
      <!-- <h2 class="text-3xl font-bold mb-8 text-center">Giveaway Winners</h2>
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <Card v-for="(winner, i) in winners" :key="i" class="text-center hover:shadow-lg transition-shadow">
          <CardHeader class="justify-center">
            <Avatar>
              <AvatarImage src="/placeholder.svg" alt="Winner avatar" />
              <AvatarFallback>{{ winner.winner.charAt(0) }}</AvatarFallback>
            </Avatar>
          </CardHeader>
          <CardContent>
            <CardTitle class="text-lg truncate">{{ winner.winner }}</CardTitle>
            <p class="text-muted-foreground text-sm">Prize: {{ winner.prize }}</p>
          </CardContent>
        </Card>
      </div> -->
   
      
      <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="giveaway in filteredGiveaways"
          :key="giveaway.id"
          class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow bg-card"
        >
          
          <div class="relative aspect-video bg-secondary p-">
            <img
              :src="giveaway.image"
              :alt="giveaway.skinName"
              class="w-full h-full object-contain"
            />
            <div class="absolute top-2 right-2">
              <span
                :class="[
                  'px-2 py-1 rounded text-xs font-medium',
                  giveaway.rarity === '4b69ff' ? 'bg-blue-500 text-white' :
                  giveaway.rarity === 'Covert' ? 'bg-red-500 text-white' :
                  giveaway.rarity === 'Classified' ? 'bg-pink-500 text-white' :
                  giveaway.rarity === 'Restricted' ? 'bg-purple-500 text-white' :
                  'bg-blue-500 text-white'
                ]"
              >
                {{ giveaway.rarity }}
              </span>
            </div>
          </div>

          
          <div class="p-4">
            <h3 class="font-semibold text-lg mb-1 line-clamp-1">{{ giveaway.skin_name }}</h3>
            <p class="text-muted-foreground text-sm mb-3">Weapon</p>
            
            <div class="flex justify-between items-center mb-4">

             <div class="flex flex-col">
              <p class="text-xs text-muted-foreground">Market Value</p>
              <div class="flex items-center bg-secondary rounded-md mt-2 p-2">
                <DollarSign class="w-4 h-4"/>
                <p class="font-medium">{{ giveaway.value }}</p> 
              </div>
             </div>

             <div class="flex flex-col">
              <p class="text-xs text-muted-foreground">Total Entries:</p>
              <div class="flex items-center bg-secondary rounded-md mt-2 p-2">
                <Users class="w-5 h-5 mr-2" />
                <p class="font-medium">{{ giveaway.entries.toLocaleString() }} / {{ giveaway.max_entries.toLocaleString() }}</p>
             </div>
              </div>
            </div>

            <div class="mb-4">
              <p class="text-sm font-medium mb-2">Requirements:</p>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="req in giveaway.requirements"
                  :key="req"
                  class="bg-muted text-muted-foreground px-2 py-1 rounded text-xs"
                >
                  {{ req }}
                </span>
              </div>
            </div>

            <div class="mb-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Ends in:</span>
                <span class="font-medium" :class="giveaway.timeRemaining">
                  {{ giveaway.timeRemaining }}
                </span>
              </div>
              <div class="w-full bg-muted rounded-full h-2 mt-2">
                <Progress v-model="giveaway.progress" class="w-5/5">
                   
                </Progress>
              </div>
            </div>

          </div>
        </div>
      </div> -->
      
     </div>

    </AppLayout>
</template>

<!-- <script setup>
import { Button } from '@/components/ui/button';
import {Card,
  CardHeader,
  CardContent,
  CardFooter,
  CardTitle, } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

const giveaways = [
  {
    badge: "DAILY",
    title: "Daily Giveaway",
    description: "Desert Eagle | Trigger Discipline (Battle-Scarred)",
    image: "https://community.akamai.steamstatic.com/economy/image/i0CoZ81Ui0m-9KwlBY1L_18myuGuq1wfhWSaZgMttyVfPaERSR0Wqmu7LAocGIGz3UqlXOLrxM-vMGmW8VNxu5Dx60noTyLwiYbf_jdk7uW-V7JkMPWBMWqVxedjva85Girklhl2sWuAmYusICqWbQJxCcclRe4C4Ba5ldOyYeri7gKLg95E02yg2f7FnVOo/360fx360f",
  },
  {
    badge: "WEEKLY",
    title: "Weekly Giveaway",
    description: "AWP | Atheris (Well-Worn)",
    image: "/placeholder.svg",
  },
  {
    badge: "MONTHLY",
    title: "Monthly Giveaway",
    description: "P250 | Asiimov (Field-Tested)",
    image: "/placeholder.svg",
  },
]

const winners = [
  { winner: "Emil90 Skinrave.gg", prize: "$3.84" },
  { winner: "Unknown", prize: "$5.08" },
  { winner: "Unknown", prize: "$3.74" },
  { winner: "Unknown", prize: "$0.38" },
  { winner: "Unknown", prize: "$2.50" },
]
</script> -->


