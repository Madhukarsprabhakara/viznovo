<template>
    <form @submit.prevent="submit" class="space-y-4">
        <label for="file-upload"
            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
            @dragover.prevent @drop.prevent="onDrop">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-8m0 0l-4 4m4-4l4 4m-8 4h8" />
            </svg>
            <span class="text-sm text-gray-600">Click to select or drag & drop files here</span>
            <input id="file-upload" type="file" class="hidden" multiple @change="onFileChange" accept=".csv,.pdf" />
        </label>
        <div v-if="files.length" class="mt-2">
            <div class="text-xs text-gray-500 mb-1">Selected files:</div>
            <ul class="list-disc pl-5 space-y-1">
                <li v-for="(file, idx) in files" :key="file.name" class="text-sm text-gray-700 flex items-center">
                    <span class="flex-1 truncate">{{ file.name }}</span>
                    <button type="button" class="ml-2 text-red-500 hover:text-red-700 text-xs font-bold"
                        @click="removeFile(idx)" aria-label="Remove file">
                        &times;
                    </button>
                </li>
            </ul>
        </div>
         <div v-if="form.progress" class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-200"
                :style="{ width: form.progress.percentage + '%' }">
            </div>
        </div>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground shadow-xs hover:bg-primary/60 text-white text-sm font-medium rounded  transition"
            :disabled="!files.length || form.processing">
            <span v-if="form.processing">{{ form.progress ? form.progress.percentage + '%' : 'Uploading...' }}</span>
            <span v-else>Upload Files</span>
        </button>
        
        <div v-if="form.errors.files" class="text-red-600 text-sm mt-2">
            {{ form.errors.files }}
        </div>
        <div v-if="form.errors['files.0']" class="text-red-600 text-sm mt-1">
            {{ form.errors['files.0'] }}
        </div>
        <div v-if="form.errors.error" class="text-red-600 text-sm mt-1">
            {{ form.errors.error }}
        </div>
    </form>
</template>

<script setup lang="ts">
import { ref, defineProps, watch } from 'vue';
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
    project_id: number | string
}>();

const files = ref<File[]>([]);
const progress = ref<number | null>(null);

const form = useForm({
    project_id: props.project_id,
    files: [] as File[],
});

// Watch files and update form.files
watch(files, (newFiles) => {
    form.files = newFiles;
});

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        const newFiles = Array.from(input.files);
        const existingNames = new Set(files.value.map(f => f.name));
        files.value = [
            ...files.value,
            ...newFiles.filter(f => !existingNames.has(f.name))
        ];
    }
}

function onDrop(event: DragEvent) {
    if (event.dataTransfer?.files) {
        const newFiles = Array.from(event.dataTransfer.files);
        const existingNames = new Set(files.value.map(f => f.name));
        files.value = [
            ...files.value,
            ...newFiles.filter(f => !existingNames.has(f.name))
        ];
    }
}
function removeFile(idx: number) {
    files.value.splice(idx, 1);
}
function submit() {
    form.post(`/projects/${props.project_id}/upload`, {
        forceFormData: true,
        onProgress: (e) => {
            progress.value = e ? Math.round((e.loaded / e.total) * 100) : null;
        },
        onSuccess: () => {
            form.reset();
            files.value = [];
            progress.value = null;
        },
        onError: () => {
            progress.value = null;
        }
    })
}
</script>