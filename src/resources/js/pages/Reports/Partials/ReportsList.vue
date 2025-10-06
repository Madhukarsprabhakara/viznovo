<template>
  <div class="flex flex-col mt-6 items-center">
    <ul role="list" class="space-y-6 w-full">
      <li
        v-for="report in $page.props.reports"
        :key="report.id"
        class=" rounded-xl outline outline-1 outline-gray-200 mx-auto "
      >
        <ReportCard :report="report" />
        
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ReportCard from './ReportCard.vue'
const openDropdown = ref(null)

function toggleDropdown(id) {
  openDropdown.value = openDropdown.value === id ? null : id
}


</script>