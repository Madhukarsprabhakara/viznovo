<template>
  <div class="px-4 py-5 sm:px-6">
    <div class="flex space-x-3">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900">
          <a :href="report.authorUrl" class="hover:underline">{{ report.title }}</a>
        </p>
        <p class="text-sm text-gray-500">
          <a href="#" class="hover:underline">{{ report.date }}</a>
        </p>
      </div>
      <div class="flex shrink-0 self-center gap-2">
        <!-- Copy to Clipboard Badge Button -->
         
        <span
          class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
          <ExternalLink class="mr-1 size-4" aria-hidden="true" />

          {{ `/reports/${report.id}` }}
        </span>
        <Menu as="div" class="relative inline-block text-left">
          <MenuButton
            class="relative flex items-center rounded-full text-gray-400 outline-offset-[6px] hover:text-gray-600">
            <span class="absolute -inset-2" />
            <span class="sr-only">Open options</span>
            <MoreVertical class="size-5" aria-hidden="true" />
          </MenuButton>
          <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <MenuItems
              class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg outline outline-1 outline-black/5">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                <Link :href="`/reports/${report.id}/edit`"
                  :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'flex px-4 py-2 text-sm']">
                  <Edit class="mr-3 size-5 text-gray-400" aria-hidden="true" />
                  <span>Edit</span>
                </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                <a href="#"
                  :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'flex px-4 py-2 text-sm']">
                  <Trash class="mr-3 size-5 text-gray-400" aria-hidden="true" />
                  <span>Embed</span>
                </a>
                </MenuItem>
                
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { MoreVertical, Edit, Trash, Flag, ExternalLink } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  report: {
    type: Object,
    required: true,
  }
})


</script>