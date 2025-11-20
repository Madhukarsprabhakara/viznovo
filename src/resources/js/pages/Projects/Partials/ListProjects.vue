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
      
        <Project :project="project" /> 
      
    </div>
    
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Project from './Project.vue'
const projects = defineProps({
  projects: {
    type: Array,
    required: true,
  },
})
</script>