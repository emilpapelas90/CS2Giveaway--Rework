<script setup lang="ts">
import AppAdminSidebarLayout from '@/layouts/app/AppAdminSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppAdminSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppAdminSidebarLayout>
</template>
