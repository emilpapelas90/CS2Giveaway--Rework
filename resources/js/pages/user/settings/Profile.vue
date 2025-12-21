<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import InputError from '@/components/InputError.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Users, Gift } from 'lucide-vue-next';
import moment from 'moment';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    user:{
      id: number;
      name: string;
      email: string;
      email_verified_at: string | null;
      giveaways_won: number;
    };
}

defineProps<Props>();

const page = usePage();
const loading = ref(false)
const user = page.props.auth.user;

const connect = () => {
  window.location.href = route('discord.connect');
}

const formatDate = (date: string | Date) => {
  return moment(date).format('MM/D/YYYY, h:mm:ss a');
}

const form = useForm({
  name: user.name,
  email: user.email,
  steamTradeUrl: user.steamTradeUrl ? 0 : 0,
});

const submit = () => {
  form.patch(route('profile.update'), {
    preserveScroll: true,
  });
};
</script>

<template>
 <NewAppLayout>
  <SettingsLayout>

    <!-- <div class="mb-5">
      <h1 class="text-3xl font-bold text-white mb-2">Profile Settings</h1>
      <p class="text-gray-300 text-lg">Manage your account preferences and notifications</p>
    </div> -->

    <HeadingSmall title="Profile information" description="Update your name and email address" />

    <!-- <div v-if="status === 'verification-link-sent'" class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 text-sm text-center text-green-500 font-medium p-3">
      A new verification link has been sent to your email address.
    </div> -->

    <form @submit.prevent="submit" class="space-y-6">
     <!-- Profile Settings -->
     <div class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6">
    
      <div class="space-y-6">
        <!-- Profile Picture -->

       <div class="flex flex-col md:flex-row items-center justify-between">

        <div class="flex flex-col items-center md:flex-row md:space-x-6">
         <img :src="`https://ui-avatars.com/api/?name=${user.name}`" class="w-12 h-12 mb-2 md:mb-0 rounded"/>
         <div class="text-center md:text-start">
          <p class="text-white text-xl mt-1">{{ user.name}}</p>
          <p class="text-gray-400 text-sm mt-1">{{ user.email}}</p>
         </div>
        </div>

        
        <div class="flex items-center mt-2 md:mt-0">
          <span>Giveaways Won: {{ user.giveaways_won }}</span>
        </div>

       </div>

         <div>
          <label class="block text-white font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.name" />
         </div>

         <div>
          <label class="block text-white font-medium mb-1">Email Address</label>
          <input v-model="form.email" type="email" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.email" />
         </div>

         <div>
          <label class="block text-white font-medium mb-1">Steam Trade URL</label>
          <input v-model="form.steamTradeUrl" type="url" placeholder="https://steamcommunity.com/tradeoffer/new/?partner=..." class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.steamTradeUrl" />
          <a href="https://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url" target="_blank" class="text-gray-400 hover:text-gray-200 text-sm flex mt-2 justify-end">Get it from here.</a>
         </div>

         <div v-if="!user.email_verified_at" class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-4">
            <p class="flex items-center justify-between mt- text-sm">
                <span v-if="!status">Your email address is unverified.</span>
                <span v-else class="text-green-500">Verification email sent!</span>

                <Link :href="route('verification.send')" method="post"  as="button" preserve-scroll @start="loading = true" @finish="loading = false"
                  class="bg-gradient-to-r from-orange-500 to-red-500 text-white text-sm font-bold py-1 px-2 rounded-md transition-all duration-200 transform focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50">
                     <!-- {{ loading ? 'Sending...' : 'Verify Now' }} -->

                     <span v-if="loading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                     </span>
                     <span v-else>Verify Now</span>
                </Link>
            </p>
         </div>

         <div v-else class="">
          <label class="block text-green-500 font-medium">Email Verified at: {{ formatDate(user.email_verified_at) }}</label>
         </div>

         
         <div class="flex items-center gap-4">
          <button :disabled="form.processing" class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 transform  focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50">Save</button>
            <!-- <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
              <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
            </Transition> -->
         </div>
      </div>

     </div>
    </form>

  </SettingsLayout>
 </NewAppLayout>
</template>