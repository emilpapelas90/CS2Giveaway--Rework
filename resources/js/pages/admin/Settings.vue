<template>
    <AdminLayout :breadcrumbs="breadcrumbs">

      <div class="space-y-6 m-4">
          <div>
            <h2 class="text-2xl font-bold mb-4">General Settings</h2>
            <Card class="p-4">
              <!-- <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="submit">
                <div>
                  <Label class="block text-sm font-medium mb-2">Site Name</Label>
                  <Input
                    
                    type="text"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="Enter site name"
                  />
                </div>
                <div>
                  <Label class="block text-sm font-medium mb-2">Site URL</Label>
                  <Input
                   
                    type="url"
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    placeholder="https://example.com"
                  />
                </div>

                <div class="col-span-1 md:col-span-4 lg:col-span-6">
                  <Button type="submit" :disabled="form.processing">Submit</Button>
                </div>
              </form> -->

              <div class="flex-1">
                <Label class="block text-sm font-medium mb-2">Below you can fetch items from your inventory to show in Giveaways (It may takes 1-2 minutes until process done.)</Label>
                 <form @submit.prevent="fetch">
                  <Button type="submit" :disabled="fetch_item.processing">Fetch Items</Button>
                 </form>
              </div>
            </Card>
          </div>
        </div>
     <Toaster/>
    </AdminLayout>
</template>
   
   
<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { useForm, usePage } from "@inertiajs/vue3";
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { toast } from 'vue-sonner';
import { ref } from 'vue';
import { icons } from 'lucide-vue-next';

const page = usePage();

const breadcrumbs = [{
  title: 'Admin',
  href: '/admin/dashboard',
},
{
  title: 'Settings',
  href: '/admin/settings',
}];

const form = useForm({
  name: '',
  active: '1',
});

const fetch_item = useForm({
  
})

const fetch = () => { form.post(route('items.fetch'), {
  preserveScroll: true,
  onSuccess: () => {
   toast('Sucesss', {
    description: page.props.flash.success });
   },
  onError: () => {
   toast('Error', {
     description: page.props.errors });
   }
})};

const statuses = ref([
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
]); 

const submit = () => { form.post(route('giveaway.store'), {
  preserveScroll: true,
  onSuccess: () => {
   toast({
    title: 'Success',
    description: page.props.flash.success,
    });
   },
   onError: (errors) => {
    console.error(errors);
   }
})};
   
</script>