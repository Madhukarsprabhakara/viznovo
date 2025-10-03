<template>
  <Link href="/projects/create"
    type="button"
    class="relative mx-auto block w-full max-w-lg rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-gray-600"
  >
    <svg class="mx-auto size-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v20c0 4.418 7.163 8 16 8 1.381 0 2.721-.087 4-.252M8 14c0 4.418 7.163 8 16 8s16-3.582 16-8M8 14c0-4.418 7.163-8 16-8s16 3.582 16 8m0 0v14m0-4c0 4.418-7.163 8-16 8S8 28.418 8 24m32 10v6m0 0v6m0-6h6m-6 0h-6" />
    </svg>
    <span class="mt-2 block text-sm font-semibold text-gray-900">Get started by creating a new Project</span>
    <span class="mt-1 block text-xs text-gray-400">Projects help group different PDFs and CSVs together, allowing us to analyze data and generate reports from multiple files.</span>
  </Link>
</template>
<script setup>
import { Link, usePage } from '@inertiajs/vue3';

</script>