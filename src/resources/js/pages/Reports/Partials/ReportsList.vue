<template>
  <div class="flex flex-col mt-6 items-center">
    <ul role="list" class="space-y-6 w-full">
      <li
        v-for="client in clients"
        :key="client.id"
        class="overflow-hidden rounded-xl outline outline-1 outline-gray-200 mx-auto w-3/4"
      >
        <!-- ...rest of your card code... -->
        <div class="flex items-center gap-x-4 border-b border-gray-900/5 bg-gray-50 p-6">
          
          <div class="text-sm/6 font-medium text-gray-900">{{ client.name }}</div>
          <div class="relative ml-auto">
            <button
              class="relative block text-gray-400 hover:text-gray-500 focus:outline-none"
              @click="toggleDropdown(client.id)"
            >
              <span class="absolute -inset-2.5" />
              <span class="sr-only">Open options</span>
              <MoreVertical class="size-5" aria-hidden="true" />
            </button>
            <div
              v-if="openDropdown === client.id"
              class="absolute right-0 z-10 mt-0.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg outline outline-1 outline-gray-900/5"
            >
              <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900 hover:bg-gray-50">View<span class="sr-only">, {{ client.name }}</span></a>
              <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900 hover:bg-gray-50">Edit<span class="sr-only">, {{ client.name }}</span></a>
            </div>
          </div>
        </div>
        
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { MoreVertical } from 'lucide-vue-next'

const openDropdown = ref(null)

function toggleDropdown(id) {
  openDropdown.value = openDropdown.value === id ? null : id
}

const clients = [
  {
    id: 1,
    name: 'Tuple',
    imageUrl: 'https://tailwindcss.com/plus-assets/img/logos/48x48/tuple.svg',
    lastInvoice: { date: 'December 13, 2022', dateTime: '2022-12-13', amount: '$2,000.00', status: 'Overdue' },
  },
  {
    id: 2,
    name: 'SavvyCal',
    imageUrl: 'https://tailwindcss.com/plus-assets/img/logos/48x48/savvycal.svg',
    lastInvoice: { date: 'January 22, 2023', dateTime: '2023-01-22', amount: '$14,000.00', status: 'Paid' },
  },
  {
    id: 3,
    name: 'Reform',
    imageUrl: 'https://tailwindcss.com/plus-assets/img/logos/48x48/reform.svg',
    lastInvoice: { date: 'January 23, 2023', dateTime: '2023-01-23', amount: '$7,600.00', status: 'Paid' },
  },
]
</script>