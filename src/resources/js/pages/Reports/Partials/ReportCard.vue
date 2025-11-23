<template>
  <div class="px-4  py-5 sm:px-6">
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
        <a
          :href="`/reports/${report.uuid}`"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center rounded-md  text-orange-700 hover:bg-blue-50"
          :title="`Open public report /reports/${report.uuid}`"
        >
          <ExternalLink class="size-4" aria-hidden="true" />
        </a>
        
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
                  <button
                    type="button"
                    @click="confirmDelete"
                    :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'flex px-4 py-2 text-sm items-center w-full text-left']"
                  >
                    <Trash2 class="mr-3 size-5 text-red-500" aria-hidden="true" />
                    <span>Delete</span>
                  </button>
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
import { MoreVertical, Edit, Trash2, ExternalLink } from 'lucide-vue-next'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  report: {
    type: Object,
    required: true,
  }
})

const form = useForm()

const deleteReportFinally = (reportId) => {
  form.delete(`/reports/${reportId}`, {
    errorBag: 'deleteReport',
    onSuccess: () => {
      // optional: show client-side feedback or rely on server redirect/flash
      // e.g. console.log('Deleted')
    },
    onError: () => {
      alert('Failed to delete report. Please try again.')
    },
  })
}

/**
 * Confirm and delete report via form.delete to /reports/{id}
 */
function confirmDelete() {
  const title = props.report?.title ?? 'this report'
  const ok = confirm(`Delete report "${title}"? This action cannot be undone.`)
  if (!ok) return

  deleteReportFinally(props.report.id)
}
</script>