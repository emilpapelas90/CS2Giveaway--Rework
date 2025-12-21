<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center space-y-2 py-8">
        <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-orange-500 to-red-600 bg-clip-text text-transparent">
          CS2 Weapon Giveaway
        </h1>
        <p class="text-slate-400 text-lg">Add participants and draw a winner with style</p>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Add Participants -->
        <Card class="lg:col-span-1 bg-slate-900/50 border-slate-800">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <UserPlus class="w-5 h-5 text-orange-500" />
              Add Participant
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label for="participant-name">Participant Name</Label>
              <Input
                id="participant-name"
                v-model="newParticipant"
                placeholder="Enter name..."
                @keyup.enter="addParticipant"
                class="bg-slate-800 border-slate-700 focus:border-orange-500"
              />
            </div>
            <Button 
              @click="addParticipant" 
              class="w-full bg-orange-600 hover:bg-orange-700"
              :disabled="!newParticipant.trim()"
            >
              <Plus class="w-4 h-4 mr-2" />
              Add to Giveaway
            </Button>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 pt-4">
              <Card class="bg-slate-800/50 border-slate-700">
                <CardContent class="p-4 text-center">
                  <div class="text-2xl font-bold text-orange-500">{{ participants.length }}</div>
                  <div class="text-xs text-slate-400">Participants</div>
                </CardContent>
              </Card>
              <Card class="bg-slate-800/50 border-slate-700">
                <CardContent class="p-4 text-center">
                  <div class="text-2xl font-bold text-blue-500">{{ winners.length }}</div>
                  <div class="text-xs text-slate-400">Winners</div>
                </CardContent>
              </Card>
            </div>
          </CardContent>
        </Card>

        <!-- Middle Column - Giveaway Wheel -->
        <Card class="lg:col-span-2 bg-slate-900/50 border-slate-800">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span class="flex items-center gap-2">
                <Trophy class="w-5 h-5 text-orange-500" />
                Draw Winner
              </span>
              <Badge v-if="isSpinning" variant="secondary" class="bg-orange-600">
                Drawing...
              </Badge>
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Winner Animation Area -->
            <div class="relative bg-slate-800/50 rounded-lg p-8 min-h-[300px] flex items-center justify-center border-2 border-slate-700">
              <!-- Particles -->
              <div v-if="showParticles" class="absolute inset-0 overflow-hidden pointer-events-none">
                <div 
                  v-for="i in 20" 
                  :key="i"
                  class="absolute w-2 h-2 bg-orange-500 rounded-full animate-particle"
                  :style="{
                    left: `${Math.random() * 100}%`,
                    top: `${Math.random() * 100}%`,
                    animationDelay: `${Math.random() * 0.5}s`
                  }"
                />
              </div>

              <div v-if="!isSpinning && !winner && participants.length === 0" class="text-center text-slate-500">
                <Users class="w-16 h-16 mx-auto mb-4 opacity-50" />
                <p class="text-lg">Add participants to start the giveaway</p>
              </div>

              <div v-else-if="!isSpinning && !winner && participants.length > 0" class="text-center">
                <Trophy class="w-20 h-20 mx-auto mb-4 text-orange-500 opacity-50" />
                <p class="text-xl text-slate-400">Ready to draw a winner!</p>
                <p class="text-sm text-slate-500 mt-2">{{ participants.length }} participants waiting</p>
              </div>

              <div v-else-if="isSpinning" class="text-center w-full">
                <div class="text-6xl font-bold text-white mb-4 animate-pulse">
                  {{ currentHighlight }}
                </div>
                <div class="flex justify-center gap-2">
                  <div v-for="i in 3" :key="i" class="w-3 h-3 bg-orange-500 rounded-full animate-bounce" :style="{ animationDelay: `${i * 0.1}s` }" />
                </div>
              </div>

              <div v-else-if="winner" class="text-center relative">
                <div class="absolute inset-0 bg-orange-500/20 blur-3xl animate-pulse" />
                <Trophy class="w-24 h-24 mx-auto mb-6 text-orange-500 animate-bounce relative z-10" />
                <div class="relative z-10">
                  <div class="text-2xl text-orange-500 mb-2 font-semibold">Winner!</div>
                  <div class="text-5xl font-bold mb-4 bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
                    {{ winner }}
                  </div>
                  <Badge class="bg-orange-600 text-white px-4 py-1">
                    <Crown class="w-4 h-4 mr-1 inline" />
                    Champion
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
              <Button 
                @click="startSpin" 
                :disabled="participants.length === 0 || isSpinning"
                class="flex-1 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-lg py-6"
              >
                <Sparkles class="w-5 h-5 mr-2" />
                {{ isSpinning ? 'Drawing...' : 'Draw Winner' }}
              </Button>
              <Button 
                v-if="winner"
                @click="reset" 
                variant="outline"
                class="border-slate-700 hover:bg-slate-800"
              >
                <RotateCcw class="w-5 h-5" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Bottom Section -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Participants List -->
        <Card class="bg-slate-900/50 border-slate-800">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span class="flex items-center gap-2">
                <Users class="w-5 h-5 text-orange-500" />
                Participants ({{ participants.length }})
              </span>
              <Button 
                v-if="participants.length > 0"
                @click="clearAll" 
                variant="ghost" 
                size="sm"
                class="text-red-400 hover:text-red-300 hover:bg-red-950/50"
              >
                <Trash2 class="w-4 h-4 mr-1" />
                Clear All
              </Button>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ScrollArea class="h-[300px]">
              <div v-if="participants.length === 0" class="text-center py-12 text-slate-500">
                <UserPlus class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No participants yet</p>
              </div>
              <div v-else class="space-y-2">
                <div
                  v-for="(participant, index) in participants"
                  :key="index"
                  class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg border border-slate-700 hover:border-orange-500/50 transition-colors group"
                  :class="{ 
                    'border-orange-500 bg-orange-500/10 scale-105': isSpinning && participant === currentHighlight,
                    'animate-pulse-fast': isSpinning && participant === currentHighlight
                  }"
                >
                  <div class="flex items-center gap-3">
                    <Avatar class="w-8 h-8 bg-slate-700">
                      <AvatarFallback class="bg-orange-600 text-white text-sm">
                        {{ participant.charAt(0).toUpperCase() }}
                      </AvatarFallback>
                    </Avatar>
                    <span class="font-medium">{{ participant }}</span>
                  </div>
                  <Button
                    @click="removeParticipant(index)"
                    variant="ghost"
                    size="sm"
                    class="opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 hover:text-red-400"
                  >
                    <X class="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </ScrollArea>
          </CardContent>
        </Card>

        <!-- Winners History -->
        <Card class="bg-slate-900/50 border-slate-800">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Crown class="w-5 h-5 text-orange-500" />
              Winners History
            </CardTitle>
          </CardHeader>
          <CardContent>
            <!-- <ScrollArea class="h-[300px]">
              <div v-if="winners.length === 0" class="text-center py-12 text-slate-500">
                <Trophy class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No winners yet</p>
              </div>
              <div v-else class="space-y-2">
                <div
                  v-for="(winner, index) in winners"
                  :key="index"
                  class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-900/20 to-transparent rounded-lg border border-orange-500/30"
                >
                  <div class="flex items-center gap-3">
                    <Badge variant="secondary" class="bg-orange-600">
                      #{{ winners.length - index }}
                    </Badge>
                    <Crown class="w-4 h-4 text-orange-500" />
                    <span class="font-medium">{{ winner }}</span>
                  </div>
                  <span class="text-xs text-slate-500">Latest</span>
                </div>
              </div>
            </ScrollArea> -->
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
//import { ScrollArea } from '@/components/ui/scroll-area'
import { 
  Trophy, Users, Crown, Plus, X, Sparkles, 
  RotateCcw, Trash2, UserPlus 
} from 'lucide-vue-next'

const newParticipant = ref('')
const participants = ref<string[]>(['SteelWarrior', 'NeonStrike', 'PhantomAce', 'VortexGaming', 'ShadowHunter'])
const isSpinning = ref(false)
const winner = ref('')
const winners = ref<string[]>([])
const currentHighlight = ref('')
const showParticles = ref(false)

const addParticipant = () => {
  if (newParticipant.value.trim()) {
    participants.value.push(newParticipant.value.trim())
    newParticipant.value = ''
  }
}

const removeParticipant = (index: number) => {
  participants.value.splice(index, 1)
}

const clearAll = () => {
  participants.value = []
  winner.value = ''
}

const startSpin = () => {
  if (participants.value.length === 0 || isSpinning.value) return
  
  isSpinning.value = true
  winner.value = ''
  showParticles.value = false
  
  let currentIndex = 0
  let speed = 100
  const minSpeed = 20
  const acceleration = 0.95
  let elapsed = 0
  const spinDuration = 4000
  
  const interval = setInterval(() => {
    currentHighlight.value = participants.value[currentIndex]
    currentIndex = (currentIndex + 1) % participants.value.length
    elapsed += speed
    
    if (elapsed < spinDuration * 0.7) {
      speed = Math.max(minSpeed, speed * acceleration)
    } else {
      speed = Math.min(200, speed * 1.15)
    }
    
    if (elapsed >= spinDuration) {
      clearInterval(interval)
      
      const winnerIndex = Math.floor(Math.random() * participants.value.length)
      winner.value = participants.value[winnerIndex]
      winners.value.unshift(winner.value)
      currentHighlight.value = ''
      
      showParticles.value = true
      setTimeout(() => {
        isSpinning.value = false
        showParticles.value = false
      }, 100)
      
      participants.value.splice(winnerIndex, 1)
    }
  }, speed)
}

const reset = () => {
  winner.value = ''
  currentHighlight.value = ''
  showParticles.value = false
}
</script>

<style scoped>
@keyframes particle {
  0% {
    transform: translate(0, 0) scale(1);
    opacity: 1;
  }
  100% {
    transform: translate(var(--tx, 100px), var(--ty, -100px)) scale(0);
    opacity: 0;
  }
}

.animate-particle {
  --tx: calc((random() - 0.5) * 200px);
  --ty: calc((random() - 0.5) * 200px);
  animation: particle 1s ease-out forwards;
}

.animate-pulse-fast {
  animation: pulse 0.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>