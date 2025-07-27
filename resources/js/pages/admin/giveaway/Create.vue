<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
     <Card class="m-4">
       <CardContent>
         <form class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 pt-6" @submit.prevent="submit">
          
           <div class="grid gap-2 col-span-1 md:col-span-1 lg:col-span-2">
             <Label for="title">Giveaway Max Entries</Label>
             <Input v-model="form.max_entries" type="number" min="0" max="200" placeholder="Max Entries"/>
             <span v-if="form.errors.max_entries" class="text-red-600 text-xs">{{ form.errors.max_entries }}</span>
           </div>
       

           <div class="grid gap-2 col-span-1 md:col-span-2 lg:col-span-2">
             <Label for="title">End Time</Label>
             <Datepicker v-model="form.end_time" :min-date="new Date()"/>
             <span v-if="form.errors.end_time" class="text-red-600 text-xs">{{ form.errors.end_time }}</span>
           </div>
  

           <div class="grid gap-2 col-span-1 md:col-span-1 lg:col-span-1">
             <Label for="category">Giveaway Type</Label>
             <Select v-model="form.type">
              <SelectTrigger> 
                <SelectValue placeholder="Giveaway Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Type</SelectLabel>
                  <SelectItem v-for="(type, ind) in types" :key="ind" :value="type.value">{{type.label}}</SelectItem>
                </SelectGroup>
              </SelectContent>
             </Select>
             <span v-if="form.errors.type" class="text-red-600 text-xs">{{ form.errors.type }}</span>
           </div>

           <div class="grid gap-2 col-span-1 md:col-span-1 lg:col-span-1">
             <Label for="category">Giveaway Active</Label>
             <Select v-model="form.is_active">
              <SelectTrigger> 
                <SelectValue placeholder="Giveaway Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Status</SelectLabel>
                  <SelectItem v-for="(status, ind) in statuses" :key="ind" :value="status.value">{{status.label}}</SelectItem>
                </SelectGroup>
              </SelectContent>
             </Select>
             <span v-if="form.errors.is_active" class="text-red-600 text-xs">{{ form.errors.is_active }}</span>
           </div>


           <div class="grid gap-2 col-span-1 md:col-span-1 lg:col-span-6">
            <Label class="mb-1 block">Requirements</Label>
            <div v-for="(req, index) in form.requirements" :key="index" class="flex flex-col md:flex-row items-start md:items-center gap-2 mb-2">

              <!-- <template v-if="req.type === 'email'">
                <div class="flex items-center gap-2 text-sm text-green-600 font-semibold">
                  ✅ Email confirmation required
                </div>
              </template> -->

              <!-- <template v-else-if="req.type === 'discord'">
                <Select v-model="req.type" class="w-full md:w-1/3" disabled>
                  <SelectTrigger><SelectValue placeholder="Type" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="'discord'">Join Discord Server</SelectItem>
                  </SelectContent>
                </Select>

                <Input v-model="req.server_name" type="text" placeholder="Discord Server Name" class="flex-1 w-full" />
                <Input v-model="req.guild_id" type="text" placeholder="Guild ID" class="flex-1 w-full" />
                <Input v-model="req.invite" type="text" placeholder="Invite Link" class="flex-1 w-full" />

                <Button type="button" variant="destructive" @click="removeRequirement(index)">X</Button>
              </template> -->

              <Select v-model="req.type" class="w-1/3">
                <SelectTrigger><SelectValue placeholder="Type" /></SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="option in requirementOptions" :key="option.type" :value="option.type">{{ option.label }}</SelectItem>
                </SelectContent>
              </Select>

              <Input v-model="req.server_name" type="text" placeholder="Discord Server Name" class="flex-1" />
              <Input v-model="req.guild_id" type="text" placeholder="Guild ID" class="flex-1" />
              <Input v-model="req.invite" type="text" placeholder="Invite Link" class="flex-1" />

              <Button type="button" variant="destructive" @click="removeRequirement(index)">X</Button>
            </div>

            <!-- <Button type="button" @click="() => addRequirement('discord')" size="sm" variant="secondary">
              + Add Discord Requirement
            </Button> -->

            <Button type="button" @click="addRequirement" size="sm" variant="secondary">+ Add Requirement</Button>
           </div>
       
           <div class="col-span-1 md:col-span-4 lg:col-span-6">
             <Button type="submit" :disabled="form.processing">Submit</Button>
           </div>
           
         </form>

       </CardContent>
     </Card>

     <Item_select @update:selected="(val) => selectedItems = val"/>
     <Toaster/>
    </AdminLayout>
</template>
   
   
<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import Item_select from '@/pages/admin/giveaway/Item_select.vue';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { useForm, usePage } from "@inertiajs/vue3";
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue, } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';
import { ref, watch } from 'vue';
import { icons } from 'lucide-vue-next';

const page = usePage();

const breadcrumbs = [{
  title: 'Giveaways',
  href: '/admin/giveaways',
},
{
  title: 'Create',
  href: '/admin/giveaways/create',
}];

const selectedItems = ref([])

const form = useForm({
  skin_name: '',
  image: '',
  value: '',
  rarity: '',
  entries: '0',
  max_entries: '',
  requirements: [],
  end_time: null,
  type: '0',
  is_active: '1',
});

watch(() => selectedItems.value, (newItem) => {
  if (newItem) {
    form.skin_name = newItem.name
    form.image = newItem.image
    form.value = newItem.price
    form.rarity = newItem.color
    //form.end_time = date.value
  } else {
    form.skin_name = ''
    form.image = ''
    form.value = ''
    form.rarity = ''
    //form.end_time = ''
  }
})

const types = ref([
  { label: 'Daily', value: '0' },
  { label: 'Weekly', value: '1' },
  { label: 'Monthly', value: '2' },
]); 

const statuses = ref([
  { label: 'Active', value: '1' },
  { label: 'Inactive', value: '0' },
]); 


const requirementOptions = ref([
  { label: 'Join Discord Server', type: 'discord', guild_id: '', invite: '' },
]);

const addRequirement = () => {
  form.requirements.push({ label: 'Join Discord Server', type: '', server_name: '', invite: '', guild_id: '' });
};

const removeRequirement = (index) => {
  form.requirements.splice(index, 1);
};

// watch(
//   () => form.requirements,
//   (newVal) => {
//     newVal.forEach((req, index) => {
//       const selected = requirementOptions.value.find((opt) => opt.type === req.type);
//       if (selected) {
//         req.invite = selected.invite;
//         req.guild_id = selected.guild_id;
//       }
//     });
//   },
//   { deep: true }
// );

const submit = () => { 
 
 form.post(route('giveaway.store'), {
  preserveScroll: true,
  onSuccess: () => {
  //  toast({
  //   title: 'Success',
  //   description: page.props.flash.success,
  //   });
   },
   onError: (errors) => {
    console.error(errors);
   }
})};
   
</script>