<template>
  <form @submit.prevent="submit" class="space-y-3">
    <div>
      <!-- <label class="block text-sm font-medium text-gray-700 mb-1">Add website URL</label> -->
      <input
        v-model="form.url"
        type="url"
        inputmode="url"
        placeholder="https://tesla.com/esg"
        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
      />
      <div v-if="form.errors.url" class="text-red-600 text-sm mt-1">{{ form.errors.url }}</div>
    </div>

    <button
      type="submit"
      class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground shadow-xs hover:bg-primary/60 text-white text-sm font-medium rounded transition"
      :disabled="!form.url || form.processing"
    >
      <span v-if="form.processing">Adding…</span>
      <span v-else>Add URL</span>
    </button>
  </form>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  project_id: number | string
}>()

const form = useForm({
  url: '',
})

function submit() {
  form.post(`/projects/${props.project_id}/add-url`, {
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>
