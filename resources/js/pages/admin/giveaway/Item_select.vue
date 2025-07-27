<template>
   <div v-if="loading">Loading items...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-2 mx-4 mt-2">

    <label v-for="item in items" :key="item.asset_id" @click="toggleSelection(item)"
     class="relative isolate flex flex-col justify-end overflow-hidden rounded-2xl px-8 pb-8 pt-40"
       :class="['cursor-pointer border-2 transition-all duration-150', selectedItem?.asset_id === item.asset_id ? `border-gray-400` : 'border-gray-300']">

     <input type="checkbox" class="sr-only" :checked="selectedItem?.asset_id === item.asset_id"  @change="toggleSelection(item)"/>   

  <!-- Background Image with z-0 -->
        <img :src="item.image" alt="CS2 Giveaway" class="absolute inset-0 h-32 w-auto object-cover mx-auto z-10"/>

        <!-- Color overlay with opacity + z-10 -->
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40"></div>

        <!-- Content above everything -->
        <h3 class="z-20 text-xl font-bold text-white">{{ item.name }}</h3>
        <div class="z-20 mt-2 flex items-center gap-2">
        <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
            $ {{ item.price }}
        </span>
        <span class="text-white px-2 py-1 rounded-full text-xs font-medium" :style="{ backgroundColor: `#${item.color}` }">
            Classified
        </span>
        </div>
    </label>
    </div>

 <div class="bg-background mt-3">

 </div>

</template>

<script setup>
import { Card, CardContent } from '@/components/ui/card';
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';

const items = ref([]);
const loading = ref(true)
const error = ref(null)

const selectedItem = ref(null)

const toggleSelection = (item) => {
  selectedItem.value =
    selectedItem.value?.asset_id === item.asset_id ? null : item
}

watch(selectedItem, () => {
  emit('update:selected', selectedItem.value)
})


const emit = defineEmits(['update:selected'])

const fetch_item = async () => { 
  try {
    const response = await axios.get(route('items.index'))
    items.value = response.data
  } catch ( err ) {
    error.value = 'Failed to load Items.'
    console.log(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetch_item)
</script>