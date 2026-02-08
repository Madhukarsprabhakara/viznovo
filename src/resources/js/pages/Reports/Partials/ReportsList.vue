<template>

  <div class="flex flex-col mt-6 items-center">

    <ul role="list" class="space-y-6 w-3/4">
      <div class="flex justify-end items-center mt-2">
        <div class="flex">
          <Link :href="`/reports/${$page.props.project.id}/autocreate`"
            class="ml-3 inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-indigo-500 via-indigo-400 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-400 hover:via-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
            <span>Auto create using Intelligent Grid</span>
            <span class="inline-flex items-center rounded-md bg-white/15 px-2 py-0.5 text-[11px] font-semibold ring-1 ring-white/25">AI</span>
          </Link>
        </div>
        <div class="flex">
          <Link :href="`/reports/${$page.props.project.id}/create`"
            class="ml-3 inline-flex items-center rounded-md  bg-primary text-primary-foreground shadow-xs hover:bg-primary/90 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
          Create custom</Link>
        </div>
      </div>

      <li v-for="report in $page.props.reports" :key="report.id"
        class=" rounded-xl bg-gray-100 outline outline-1 outline-gray-200 mx-auto ">
        <ReportCard :report="report" />

      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ReportCard from './ReportCard.vue'
import { Link } from '@inertiajs/vue3'
const openDropdown = ref(null)

function toggleDropdown(id) {
  openDropdown.value = openDropdown.value === id ? null : id
}


</script>