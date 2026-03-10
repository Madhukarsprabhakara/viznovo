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
import { useEchoPublic } from '@laravel/echo-vue';

// ...existing code...
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Reverb Test', href: '/reverbtest' },
    
])

const lastReceivedAt = ref<string | null>(null);

useEchoPublic('tracker-created', 'TrackerCreated', () => {
    lastReceivedAt.value = new Date().toISOString();
});
// ...existing code...
</script>