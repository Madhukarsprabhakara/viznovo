<template>
  <div class="min-h-screen">
    <div
      
      v-html="reportHtml"
    ></div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const reportHtml = page.props.report
</script>