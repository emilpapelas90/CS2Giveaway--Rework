<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import InputError from '@/components/InputError.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>


<template>
 <NewAppLayout>
  <SettingsLayout>

    <!-- <div class="mb-5">
      <h1 class="text-4xl font-bold text-white mb-4">⚙️ Password Settings</h1>
      <p class="text-gray-300 text-lg">Manage your account preferences and notifications</p>
    </div> -->

    <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />


    <!-- <div v-if="status === 'verification-link-sent'" class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 text-sm text-center text-green-500 font-medium p-3">
      A new verification link has been sent to your email address.
    </div> -->

    <form @submit.prevent="updatePassword" class="space-y-6">
     <!-- Profile Settings -->
     <div class="bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-6">    
      <div class="space-y-6">
        
         <div>
          <label class="block text-white font-medium mb-1">Current password</label>
          <input v-model="form.current_password" type="password" ref="currentPasswordInput" placeholder="Current password" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.current_password" />
         </div>

         <div>
          <label class="block text-white font-medium mb-1">New password</label>
          <input v-model="form.password" type="password" ref="passwordInput" autocomplete="new-password" placeholder="New password" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.password" />
         </div>

         <div>
          <label class="block text-white font-medium mb-1">Confirm password</label>
          <input v-model="form.password_confirmation" type="url" autocomplete="new-password" placeholder="Confirm password" class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200">
          <InputError :message="form.errors.password_confirmation" />
         </div>
         
         <div class="flex items-center gap-4">
          <button :disabled="form.processing" class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 transform  focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50">Save password</button>

            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
              <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
            </Transition>
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