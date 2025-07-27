<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3';

const isMobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900">
    <!-- Header -->
    <header class="bg-black/80 backdrop-blur-md border-b border-orange-500/20 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo/Brand -->
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">CS</span>
            </div>
            <h1 class="text-xl font-bold text-white">SkinDrop</h1>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex items-center space-x-8">
            <Link :href="route('login')" :class="route().current('login') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="transition-colors duration-200 font-medium">
              Login
            </Link>
            <Link :href="route('register')" :class="route().current('register') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="transition-colors duration-200 font-medium">
              Register
            </Link>
          </nav>

          <!-- Mobile menu button -->
          <button 
            @click="toggleMobileMenu"
            class="md:hidden text-gray-300 hover:text-white focus:outline-none focus:text-white transition-colors duration-200"
          >
            <svg v-if="!isMobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Mobile Navigation -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="isMobileMenuOpen" class="md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-black/90 backdrop-blur-md rounded-lg mt-2 border border-orange-500/20">
              <Link :href="route('login')" class="block px-3 py-2 text-gray-300 hover:text-orange-400 hover:bg-gray-800/50 rounded-md transition-all duration-200 font-medium">
                How it Works
              </Link>
              <Link :href="route('register')" class="w-full mt-2 bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-full font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200">
                Join Giveaway
              </Link>
            </div>
          </div>
        </transition>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex min-h-svh flex-col items-center justify-center px-2 sm:px-6 lg:px-8">
      <slot/>
    </main>

    <!-- Footer -->
    <footer class="bg-black/60 backdrop-blur-md border-t border-orange-500/20 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto text-center">
        <div class="flex items-center justify-center space-x-2 mb-4">
          <div class="w-6 h-6 bg-gradient-to-r from-orange-500 to-red-500 rounded flex items-center justify-center">
            <span class="text-white font-bold text-xs">CS</span>
          </div>
          <span class="text-white font-bold">SkinDrop</span>
        </div>
        <p class="text-gray-400 mb-2">
          © 2024 SkinDrop. All rights reserved. Not affiliated with Valve Corporation.
        </p>
        <div class="flex justify-center space-x-6 text-sm">
          <a href="#" class="text-orange-400 hover:text-orange-300 transition-colors duration-200">Terms of Service</a>
          <a href="#" class="text-orange-400 hover:text-orange-300 transition-colors duration-200">Privacy Policy</a>
          <a href="#" class="text-orange-400 hover:text-orange-300 transition-colors duration-200">Contact</a>
        </div>
      </div>
    </footer>
  </div>
</template>