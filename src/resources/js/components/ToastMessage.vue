<template>
  <div class="rounded-md bg-blue-50 p-4">
    <div class="flex">
      <div class="shrink-0">
        <CheckCircle class="size-5 text-blue-400" aria-hidden="true" />
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-blue-800">Have coffee, talk to a friend or do something else!</h3>
        <div class="mt-2 text-sm text-blue-700">
          <p>{{ message }}</p>
        </div>
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <!-- <button type="button" class="rounded-md bg-blue-50 px-2 py-1.5 text-sm font-medium text-blue-800 hover:bg-blue-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">View status</button> -->
            <button type="button" class="ml-3 rounded-md bg-blue-50 px-2 py-1.5 text-sm font-medium text-blue-800 hover:bg-blue-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" @click="emit('dismiss')">Dismiss</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CheckCircle } from 'lucide-vue-next'

defineProps({
  message: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['dismiss'])
</script>