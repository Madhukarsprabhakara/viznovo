<template>
    <Head title="Create a Project" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen items-start justify-center">
            <div class="w-full max-w-lg  rounded-xl border-1 shadow p-8 mt-12">
                <div class="space-y-3">
                    <div>{{ $page.props.message }}</div>

                    <div>
                        <div class="text-sm font-medium">Last TrackerCreated received at</div>
                        <div>{{ lastReceivedAt ?? 'Waiting for events…' }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">Last CsvStatusUpdate received at</div>
                        <div>{{ lastCsvStatusReceivedAt ?? 'Waiting for events…' }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">CSV Status</div>
                        <div>{{ csvStatusMessage ?? 'Waiting for status…' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useEcho, useEchoPublic } from '@laravel/echo-vue';

// ...existing code...
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Reverb Test', href: '/reverbtest' },
    
])

const lastReceivedAt = ref<string | null>(null);
const csvStatusMessage = ref<string | null>(null);
const lastCsvStatusReceivedAt = ref<string | null>(null);

useEcho<{ status_message?: string }>('csv.status.update.394', 'CsvStatusUpdate', (e) => {
    csvStatusMessage.value = 'test_'+new Date().toISOString() ;
    lastCsvStatusReceivedAt.value = new Date().toISOString();
});

// useEchoPublic('tracker-created', 'TrackerCreated', () => {
//     lastReceivedAt.value = new Date().toISOString();
// });


// ...existing code...
</script>