<template>
  <div class="mt-1">
    <h2 class="text-lg font-semibold mb-2">Sources</h2>
    <ul v-if="files && files.length" class="space-y-2">
      <li v-for="file in files" :key="file.id" class="flex items-center space-x-2">
        
          {{ file.name }}
        
        <span v-if="file.type" class="text-xs text-gray-400"> ({{ file.type }} )</span>
        <button
          @click="deleteFile(file.id)"
          class="ml-2 text-red-500 hover:text-red-700 text-xs font-bold"
          aria-label="Delete file"
        >
          &times;
        </button>
      </li>
    </ul>
    <div v-else class="text-gray-400 text-sm">No files uploaded yet.</div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
const props = defineProps<{
  files: Array<{
    id: number|string,
    name: string,
    url: string,
    type?: string
  }>
}>();
const form = useForm({});



const deleteFileFinally = (fileId: number | string) => {
    form.delete('/projectdata/' + fileId, {
        errorBag: 'deleteProjectFile',
    });
};

function deleteFile(fileId: number|string) {
  // Emit an event to the parent component to handle file deletion
  // You can implement the actual deletion logic in the parent component
  // For example, making an API call to delete the file from the server
  // Here, we just emit the event with the file ID
  // You can also add a confirmation dialog before emitting the event
  if (confirm('Are you sure you want to delete this file?')) {
    // Emit the event
    // Note: You need to listen for this event in the parent component
    // and handle the deletion logic there
    // Example: <FilesList @delete-file="handleDeleteFile" />
    deleteFileFinally(fileId);
    
  }
}
</script>