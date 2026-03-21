<template>
  <div
    v-if="show"
    aria-live="assertive"
    class="pointer-events-none fixed inset-0 z-50 flex items-end px-4 py-6 sm:items-start sm:p-6"
  >
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="" leave-to-class="opacity-0">
        <div class="pointer-events-auto w-full max-w-lg rounded-lg bg-white shadow-lg outline outline-1 outline-black/5">
          <div class="p-4">
            <div class="flex items-start gap-3">
              <div class="shrink-0">
                <Info class="size-6 text-blue-500" aria-hidden="true" />
              </div>

              <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">{{ title }}</p>
                <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>

                <div class="mt-3 max-h-80 overflow-y-auto rounded-md bg-gray-50 p-3">
                  <ul v-if="normalizedLogs.length > 0" role="list" class="space-y-3">
                    <li v-for="log in normalizedLogs" :key="log.id" class="border-b border-gray-200 pb-3 last:border-b-0 last:pb-0">
                      <p class="text-sm text-gray-700">{{ log.message }}</p>
                      <p v-if="log.timestamp" class="mt-1 text-xs text-gray-500">{{ log.timestamp }}</p>
                    </li>
                  </ul>
                  <p v-else class="text-sm text-gray-500">No report logs available yet.</p>
                </div>

                <div class="mt-3 flex">
                  <button type="button" class="rounded-md text-sm font-medium text-indigo-600 hover:text-indigo-500 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500" @click="emit('close')">Dismiss</button>
                </div>
              </div>

              <div class="flex shrink-0">
                <button type="button" class="inline-flex rounded-md text-gray-400 hover:text-gray-500 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600" @click="emit('close')">
                  <span class="sr-only">Close</span>
                  <X class="size-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Info, X } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Report logs',
  },
  description: {
    type: String,
    default: '',
  },
  logs: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close'])

const normalizedLogs = computed(() => {
  return props.logs.map((log, index) => ({
    id: log.id ?? `${index}-${log.created_at ?? 'log'}`,
    message: log.display_message || log.error || 'Log entry recorded.',
    timestamp: log.created_at || '',
  }))
})
</script>