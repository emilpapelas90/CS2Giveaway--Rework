<script setup lang="ts">
 import { Button } from '@/components/ui/button';
 import { Separator } from '@/components/ui/separator';
 import { Link, usePage } from '@inertiajs/vue3';
 import { type NavItem } from '@/types';

 const page = usePage();

 const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
 <div class="px-4 py-6">
 
    <div class="flex flex-col space-y-6 lg:flex-row lg:space-y-0 lg:space-x-12">
            <aside class="w-full max-w-4xl lg:w-64">
                <nav class="flex flex-col space-y-1 space-x-0 bg-black/40 backdrop-blur-md rounded-xl border border-orange-500/20 p-2">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        class="block transition-colors duration-200 font-medium"
                        :class="['text-gray-300 hover:text-orange-400 justify-start', { 'text-orange-400 bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>


            <!-- <Separator class="my-6 md:hidden" /> -->

            <div class="flex-1 md:max-w-4xl">
                <section class="max-w-full space-y-6">
                    <slot />
                </section>
            </div>
            </div>
 </div>
</template>