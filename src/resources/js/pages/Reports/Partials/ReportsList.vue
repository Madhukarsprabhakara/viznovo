<template>

  <div class="flex flex-col mt-6 items-center">

    <ul role="list" class="space-y-6 w-3/4">
      <div class="flex justify-end items-center mt-2">
        <div class="flex">
          <Link :href="`/reports/${$page.props.project.id}/create`"
            class="ml-3 inline-flex items-center rounded-md  bg-primary text-primary-foreground shadow-xs hover:bg-primary/90 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
          Create</Link>
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