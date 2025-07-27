<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Link, usePage, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { Plus, Pencil } from 'lucide-vue-next';
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card'
import moment from 'moment';

const props = defineProps(['giveaways']);
const page = usePage();

//const { toast } = useToast()

const breadcrumbs : BreadcrumbItem[] = [{ title: 'Giveaways', href: '/admin/giveaways' }];

const form = useForm({ hash: '' });

const formatDate = (date: any) => {
  return moment(date).format('YYYY/MM/DD, h:mm');
};


const deleteWall = (hash: any) => {

  form.hash = hash;

  form.post(route('offerwall.delete'), {
    preserveScroll: true,
    onSuccess: () => {
      toast('Sucesss', {
        description: page.props.flash.success 
      });
    },
    onError: (errors) => {
      console.error(errors);
    }
  })
}

watch(() => page.props.flash.success,
  (newMessage) => {
    if (newMessage) {
     toast('Sucesss', {
        description: page.props.flash.success 
     });
    }
  },{ immediate: true }
);


</script>

<template>
 <AdminLayout :breadcrumbs="breadcrumbs">
  <div class="p-4">
    <div class="flex justify-end mb-4">
       <Link :href="route('giveaway.create')" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2">
        <Plus class="h-7 w-7 text-background mr-2"/>
       Add Giveaway</Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card v-for="giveaway in giveaways" :key="giveaway.id">
        <CardHeader></CardHeader>
        <CardContent>
         <div class="flex flex-col items-center justify-items-center space-y-2 mt-">

          <img :src="giveaway.image" class="w-auto h-auto rounded mr-2"/>

          <h2 class="text-md font-medium leading-none"> {{ giveaway.skin_name }}</h2>

          <p class="text-sm mb-1">Created: {{ formatDate(giveaway.created_at) }}</p>

          <span :class="['inline-block px-3 py-1 text-xs font-medium rounded-md', giveaway.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
           {{ giveaway.is_active == 1 ? 'Active' : 'InActive' }}
          </span>

         </div>
        </CardContent>
        <CardFooter class="flex justify-between px-6 pb-6">
          <Button variant="destructive">Delete</Button>
          <Link :href="route('giveaway.edit', giveaway.id)" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-blue-400 text-primary-foreground shadow hover:bg-blue-300 h-9 px-4 py-2">Edit</Link>
        </CardFooter>
      </Card>
    </div>

  </div>
  <Toaster/>
 </AdminLayout>
</template>