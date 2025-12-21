<script setup lang="ts">
import NewAppLayout from '@/layouts/NewAppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'


const showPassword = ref(false)

const showconfirm_Password = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const toggleconfirmPasswordVisibility = () => {
  showconfirm_Password.value = !showconfirm_Password.value
}

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
  <NewAppLayout>
    <div class="flex flex-1 items-center justify-center min-h-svh">
        <!-- Login Card -->
        <div class="w-full max-w-lg bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-8">
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
            <p class="text-gray-400 mt-2">Sign up to continue to SkinDrop</p>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Email/Username Input -->

            <div>
              <label for="name" class="block text-white font-medium mb-1">Name</label>
              <input 
                type="text" 
                id="name" 
                v-model="form.name" 
                class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200"
                placeholder="Full Name"
                required
              />
              <InputError :message="form.errors.name" />
            </div>

            <div>
              <label for="email" class="block text-white font-medium mb-1">Email</label>
              <input 
                type="text" 
                id="email" 
                v-model="form.email" 
                class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200"
                placeholder="email@example.com"
                required
              />
              <InputError :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-white font-medium mb-1">Password</label>
              </div>
              <div class="relative">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password" 
                  v-model="form.password" 
                  class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200"
                  placeholder="Password"
                  required
                />
                <InputError :message="form.errors.password" />
                <button 
                  type="button"
                  @click="togglePasswordVisibility"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition-colors duration-200"
                >
                  <svg v-if="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Password Input -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label for="confirm_password" class="block text-white font-medium mb-1">Confirm Password</label>
              </div>
              <div class="relative">
                <input 
                  :type="showconfirm_Password ? 'text' : 'password'" 
                  id="confirm_password" 
                  v-model="form.password_confirmation" 
                  class="w-full bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-colors duration-200"
                  placeholder="Confirm password"
                  required
                />
                <InputError :message="form.errors.password_confirmation" />
                <button 
                  type="button"
                  @click="toggleconfirmPasswordVisibility"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition-colors duration-200"
                >
                  <svg v-if="showconfirm_Password" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>


            <!-- Login Button -->
            <button 
              type="submit" 
              class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
              :disabled="form.processing"
            >
              <span v-if="form.processing" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing up...
              </span>
              <span v-else>Sign Up</span>
            </button>
          </form>

          <!-- Social Login -->
          <!-- <div class="mt-8">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-700"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-black/40 text-gray-400">Or continue with</span>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
              <button 
                type="button" 
                class="w-full inline-flex justify-center py-2 px-4 border border-gray-700 rounded-lg bg-gray-800 text-sm font-medium text-gray-300 hover:bg-gray-700 transition-colors duration-200"
              >
                <img src="/placeholder.svg?height=24&width=24&text=Steam" alt="Steam" class="h-5 w-5 mr-2" />
                Steam
              </button>
              <button 
                type="button" 
                class="w-full inline-flex justify-center py-2 px-4 border border-gray-700 rounded-lg bg-gray-800 text-sm font-medium text-gray-300 hover:bg-gray-700 transition-colors duration-200"
              >
                <img src="/placeholder.svg?height=24&width=24&text=Google" alt="Google" class="h-5 w-5 mr-2" />
                Google
              </button>
            </div>
          </div> -->

          <!-- Register Link -->
          <div class="mt-8 text-center">
            <p class="text-gray-400">
             Already have an account? 
              <Link :href="route('login')" class="text-orange-400 hover:text-orange-300 font-medium transition-colors duration-200">Sign in</Link>
            </p>
          </div>
        </div>
   </div>
  </NewAppLayout>
    <!-- <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </form>
    </AuthBase> -->
</template>
