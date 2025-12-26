<script setup>
import { ref } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useInitials } from '@/composables/useInitials';
import { House, Gift, LogIn , UserPlus , UserRoundPlus  } from 'lucide-vue-next';

const page = usePage()
const user = page.props.auth.user

const { getInitials } = useInitials();

const isUserMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  if (isMobileMenuOpen.value) {
    isUserMenuOpen.value = false
  }
}

const handleLogout = () => {
  // Close menus
  isUserMenuOpen.value = false
  isMobileMenuOpen.value = false
  router.flushAll();
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    isUserMenuOpen.value = false
  }
}
</script>

<template>
  <!-- bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 -->
  <div class="min-h-screen bg-[#0f1923]">
    <!-- Header -->
    <header class="bg-black/80 backdrop-blur-md border-b border-orange-500/20 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between h-16">

          <!-- Logo/Brand -->
          <Link :href="route('welcome')" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">CS</span>
            </div>
            <h1 class="text-xl font-bold text-white">SkinDrop</h1>
          </Link>



          <div class="flex items-center space-x-3">

           <div class="flex space-x-8">
            <nav class="hidden md:flex items-center space-x-4">
             <template v-if="user">
              <Link :href="route('welcome')" :class="route().current('welcome') ? 'text-orange-400 border border-orange-500/20 bg-black/40 backdrop-blur-md' : 'text-gray-300 hover:text-orange-400'" class=" transition-colors duration-200 font-medium px-4 py-2 rounded-xl"><span class="flex items-center"><House class="w-5 h-5 mr-2"/>Home</span></Link>
              <Link :href="route('giveaways')" :class="route().current('giveaways') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="flex items-center transition-colors duration-200 font-medium"><Gift class="w-5 h-5 mr-2"/>Giveaways</Link>
            <!-- <Link :href="route('winners')" class="text-gray-300 hover:text-orange-400 transition-colors duration-200 font-medium">
              Winners
            </Link> -->
            <!-- <a href="#" class="text-gray-300 hover:text-orange-400 transition-colors duration-200 font-medium">
              How it Works
            </a> -->
            <!-- <button class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-full font-semibold hover:from-orange-600 hover:to-red-600 transition-all duration-200 transform hover:scale-105">
              Join Giveaway
            </button> -->
             </template>
             <template v-else>
              <Link :href="route('login')" :class="route().current('login') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="flex items-center transition-colors duration-200 font-medium"><LogIn class="w-5 h-5 mr-2"/>Login</Link>
              <Link :href="route('register')" :class="route().current('register') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="flex items-center transition-colors duration-200 font-medium"><UserPlus class="w-5 h-5 mr-2"/>Register</Link>
             </template>
             </nav>

          <!-- Mobile menu button -->

           <div class="flex items-center space-x-4">


           </div>  

          </div>

          <!-- User Menu Dropdown (Desktop/Mobile) -->
          <div v-if="user" class="hidde md:block relative">
              <button 
                @click="toggleUserMenu"
                class="flex items-center space-x-3 text-gray-300 hover:text-white transition-colors duration-200 focus:outline-none"
              >
                <!-- User Avatar -->
                <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-orange-500/50 hover:border-orange-500 transition-colors duration-200">
                  <!-- <img :alt="user.username"
                       class="w-full h-full object-cover"> -->
                  <div  class="w-full h-full bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center">
                    <span class="text-white font-bold text-sm"> {{ getInitials(user?.name) }}</span>
                  </div>
                </div>
                
                <!-- Username & Dropdown Arrow -->
                <div class="flex items-center space-x-1">
                  <span class="font-medium">{{ user?.name }}</span>
                  <svg class="w-4 h-4 transition-transform duration-200" 
                       :class="{ 'rotate-180': isUserMenuOpen }"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              
              </button>

              <!-- User Dropdown Menu -->
              <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
              >
                <div v-if="isUserMenuOpen" class="absolute z-10 right-0 mt-2 w-56 bg-black/90 backdrop-blur-md rounded-xl border border-orange-500/20 shadow-lg py-2">
                  <!-- User Info -->
                  <div class="px-4 py-3 border-b border-gray-700">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-orange-500/50">
                        <img v-if="user.avatar" 
                             :src="user.avatar" 
                             :alt="user.username"
                             class="w-full h-full object-cover">
                        <div v-else class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center">
                          <span class="text-white font-bold">{{ getInitials(user.name) }}</span>
                        </div>
                      </div>
                      <div>
                        <p class="text-white font-medium">{{ user.name }}</p>
                        <p class="text-gray-400 text-sm truncate w-34">{{ user.email }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Menu Items -->
                  <div class="py-1">
                    <a href="#" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors duration-200">
                      <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      My Profile
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors duration-200">
                      <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                      </svg>
                      My Inventory
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors duration-200">
                      <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                      My Statistics
                    </a>
                    <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors duration-200">
                      <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      Settings
                    </Link>
                  </div>

                  <!-- Logout -->
                  <div class="border-t border-gray-700 py-1">
                    <Link 
                      method="post" :href="route('logout')" @click="handleLogout" as="button"
                      class="flex items-center w-full px-4 py-2 text-red-400 hover:text-red-300 hover:bg-gray-800/50 transition-colors duration-200">
                      <svg class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Sign Out
                    </Link>
                  </div>
                </div>
              </transition>
          </div>

          <!-- Mobile menu button -->
            <button @click="toggleMobileMenu" class="md:hidden text-gray-300 hover:text-white focus:outline-none focus:text-white transition-colors duration-200">
              <svg v-if="!isMobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

        </div>
           
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
            <div class="px-2 pt-2 pb-3 mb-4 space-y-1 rounded-lg mt-2">
             <template v-if="user">
              <Link :href="route('welcome')" :class="route().current('welcome') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="block transition-colors duration-200 font-medium">
                Home
              </Link>
              <Link :href="route('giveaways')" :class="route().current('giveaways') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="block transition-colors duration-200 font-medium">
                Giveaways
              </Link>
             </template>
             <template v-else>
              <Link :href="route('login')" :class="route().current('login') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="block transition-colors duration-200 font-medium">
                Login
              </Link>
              <Link :href="route('register')" :class="route().current('register') ? 'text-orange-400' : 'text-gray-300 hover:text-orange-400'" class="block transition-colors duration-200 font-medium">
                Register
              </Link>
             </template>
            
            </div>
          </div>
        </transition>
      
      </div>
    </header>

    <!-- Main Content -->

    

     <!-- <main class="flex min-h-[calc(100vh-80px)] flex-col items-center justify-center px-2 sm:px-6 lg:px-8">
      <slot/>
     </main> -->

    <!-- <main class="flex min-h-svh flex-col items-center justify-center px-2 sm:px-6 lg:px-8">
      <slot/>
    </main> -->
    
    <main class="mx-auto flex min-h-svh w-full max-w-7x flex-1 flex-col py-16 px-2 sm:px-6 lg:px-8">
      <slot/>
    </main>

    <!-- Footer -->
    <footer class="bg-black/60 backdrop-blur-md border-t border-orange-500/20 py-4 px-4 sm:px-6 lg:px-8">
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