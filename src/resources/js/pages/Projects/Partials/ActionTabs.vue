<template>
  <div class="relative border-b border-gray-200 pb-5 sm:pb-0">
    <div class="md:flex md:items-center md:justify-between">
      <div>
        <div class="grid grid-cols-1 sm:hidden">
          <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
          <select aria-label="Select a tab" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600">
            <option v-for="tab in tabs" :key="tab.name" :selected="tab.current">{{ tab.name }}</option>
          </select>

          <ChevronDown class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500" aria-hidden="true" />
        </div>
        <div class="hidden sm:block">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <Link v-for="tab in tabs" :key="tab.name" :href="tab.href" :class="[urlIsActive(tab.href, page.url) ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium']" :aria-current="urlIsActive(tab.href, page.url) ? 'page' : undefined">
              <component :is="tab.icon" :class="[urlIsActive(tab.href, page.url) ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 size-5']" aria-hidden="true" />
              <span>{{ tab.name }}</span>
            </Link>
          </nav>
        </div>
      </div>

      <!-- <div class="mt-3 flex md:mt-0 md:ml-4">
        <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Share</button>
        <button type="button" class="ml-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Create</button>
      </div> -->
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronDown, ChartNoAxesCombined, CreditCard, Upload, Users } from 'lucide-vue-next'
import { Link, usePage } from '@inertiajs/vue3'
import { urlIsActive } from '@/lib/utils';
import { ref, defineProps, watch } from 'vue';

const page = usePage();
const props = defineProps<{
    project_id: number | string
}>();
const tabs = [
  { name: 'File Uploads', href: '/projects/' + props.project_id, icon: Upload, current: false },
  { name: 'Intelligent Dashboards', href: '/projects/' + props.project_id + '/reports', icon: ChartNoAxesCombined, current: false },
  
]
</script>