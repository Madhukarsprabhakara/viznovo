<template>
  <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
    <!-- Create New Project Card -->
    <Link
      href="/projects/create"
      class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-400 bg-blue-50 hover:bg-blue-100 shadow transition p-6 text-center"
    >
      <div class="font-semibold text-lg text-blue-700">Create New Project</div>
      <div class="text-xs text-blue-500 mt-1">Start a new project to group your files</div>
    </Link>
    <!-- Project Cards -->
    <div
      v-for="project in projects.projects"
      :key="project.id"
      class="relative block rounded-xl bg-white shadow hover:shadow-lg transition p-6 border border-gray-200 hover:border-blue-400"
    >
      <!-- Edit/Delete Buttons -->
      <div class="absolute top-2 right-2 flex space-x-2 z-10">
        <Link
          :href="`/projects/${project.id}/edit`"
          class="text-gray-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50"
          title="Edit"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6" />
          </svg>
        </Link>
        <button
          @click="$emit('delete', project.id)"
          class="text-gray-400 hover:text-red-600 p-1 rounded hover:bg-red-50"
          title="Delete"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <Link
        :href="`/projects/${project.id}`"
        class="block focus:outline-none"
        style="min-height: 80px"
      >
        <div class="font-semibold text-lg text-gray-900 mb-1 truncate">{{ project.name }}</div>
        <div class="text-sm text-gray-500 line-clamp-3">{{ project.description }}</div>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const projects = defineProps({
  projects: {
    type: Array,
    required: true,
  },
})
</script>