<script setup>
 import NewAppLayout from '@/layouts/NewAppLayout.vue';
 import { Link } from '@inertiajs/vue3';

</script>

<template>
 <NewAppLayout>
      <section class="relative px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto text-center">
          <h2 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Win Epic 
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-500">
              CS2 Skins
            </span>
          </h2>
          <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
            Join thousands of players in our daily giveaways. From rare knives to legendary AK-47 skins, 
            your next favorite weapon could be just one click away!
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <button class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 transform hover:scale-105 shadow-lg">
              🎁 Enter Giveaway Now
            </button>
            <button class="border-2 border-orange-500 text-orange-400 px-8 py-4 rounded-full font-bold text-lg hover:bg-orange-500 hover:text-white transition-all duration-200">
              👑 View Active Drops
            </button>
          </div>
        </div>
      </section>

      <!-- Stats Section -->
      <section class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20">
              <div class="text-4xl font-bold text-orange-400 mb-2">50K+</div>
              <div class="text-gray-300 font-medium">Active Players</div>
            </div>
            <div class="text-center p-6 bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20">
              <div class="text-4xl font-bold text-orange-400 mb-2">$2M+</div>
              <div class="text-gray-300 font-medium">Skins Given Away</div>
            </div>
            <div class="text-center p-6 bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20">
              <div class="text-4xl font-bold text-orange-400 mb-2">24/7</div>
              <div class="text-gray-300 font-medium">New Giveaways</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Featured Giveaways Section -->
      <section class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
          <h3 class="text-3xl font-bold text-white text-center mb-12">
            🔥 Featured Giveaways
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Giveaway Card 1 -->
            <div class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6 hover:border-orange-500/40 transition-all duration-300 transform hover:scale-105">
              <div class="w-full h-48 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-lg mb-4 flex items-center justify-center border border-orange-500/30">
                <div class="text-center">
                  <div class="text-6xl mb-2">🔪</div>
                  <div class="text-orange-400 font-semibold">Karambit</div>
                </div>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">Karambit Fade</h4>
              <p class="text-gray-400 mb-4">Factory New • $2,500 Value</p>
              <div class="flex justify-between items-center mb-4">
                <span class="text-orange-400 font-semibold">1,247 Entries</span>
                <span class="text-gray-400 text-sm">⏰ 2h 15m left</span>
              </div>
              <button class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-2 rounded-lg font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200">
                Enter Now
              </button>
            </div>

            <!-- Giveaway Card 2 -->
            <div class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6 hover:border-orange-500/40 transition-all duration-300 transform hover:scale-105">
              <div class="w-full h-48 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg mb-4 flex items-center justify-center border border-blue-500/30">
                <div class="text-center">
                  <div class="text-6xl mb-2">🔫</div>
                  <div class="text-blue-400 font-semibold">AK-47</div>
                </div>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">AK-47 Redline</h4>
              <p class="text-gray-400 mb-4">Field-Tested • $85 Value</p>
              <div class="flex justify-between items-center mb-4">
                <span class="text-orange-400 font-semibold">3,891 Entries</span>
                <span class="text-gray-400 text-sm">⏰ 5h 42m left</span>
              </div>
              <button class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-2 rounded-lg font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200">
                Enter Now
              </button>
            </div>

            <!-- Giveaway Card 3 -->
            <div class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6 hover:border-orange-500/40 transition-all duration-300 transform hover:scale-105">
              <div class="w-full h-48 bg-gradient-to-br from-green-500/20 to-teal-500/20 rounded-lg mb-4 flex items-center justify-center border border-green-500/30">
                <div class="text-center">
                  <div class="text-6xl mb-2">🎯</div>
                  <div class="text-green-400 font-semibold">AWP</div>
                </div>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">AWP Dragon Lore</h4>
              <p class="text-gray-400 mb-4">Battle-Scarred • $1,200 Value</p>
              <div class="flex justify-between items-center mb-4">
                <span class="text-orange-400 font-semibold">892 Entries</span>
                <span class="text-gray-400 text-sm">⏰ 1d 3h left</span>
              </div>
              <button class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-2 rounded-lg font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200">
                Enter Now
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- How It Works Section -->
      <section class="py- px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
          <h3 class="text-3xl font-bold text-white text-center mb-12">
            ⚡ How It Works
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6">
              <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">1️⃣</span>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">Choose Giveaway</h4>
              <p class="text-gray-400">Browse our active giveaways and pick your favorite CS2 skin</p>
            </div>
            <div class="text-center p-6">
              <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">2️⃣</span>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">Enter for Free</h4>
              <p class="text-gray-400">Click enter and you're automatically entered into the giveaway</p>
            </div>
            <div class="text-center p-6">
              <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl">3️⃣</span>
              </div>
              <h4 class="text-xl font-bold text-white mb-2">Win & Enjoy</h4>
              <p class="text-gray-400">Winners are selected randomly and skins are delivered instantly</p>
            </div>
          </div>
        </div>
      </section>
  </NewAppLayout>
</template>