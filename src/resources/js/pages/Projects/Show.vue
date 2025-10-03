<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { ref, onMounted, defineProps } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import UploadFile from './Partials/UploadFile.vue';
const props = defineProps({ project: Object })


const breadcrumbs = ref<BreadcrumbItem[]>([
    {
        title: 'Projects',
        href: '/projects',
    },
]);

const cardCollapsed = ref(false);

const LOCAL_KEY = 'dashboardCardCollapsed';

function toggleCard() {
    cardCollapsed.value = !cardCollapsed.value;
    localStorage.setItem(LOCAL_KEY, cardCollapsed.value ? '1' : '0');
}


onMounted(() => {
    const stored = localStorage.getItem(LOCAL_KEY);
    cardCollapsed.value = stored === '1' ? true : false;

    breadcrumbs.value.push({
        title: props.project.name,
        href: '/projects/' + props.project.id, // Use Ziggy route helper if available
    });

});

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Collapsible Card -->
            <div class="mb-2">
                <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 shadow p-4 transition-all duration-300"
                    :class="cardCollapsed ? 'h-20 overflow-hidden' : ''">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-lg">{{ project.name }} -- File Uploads</div>
                            <div class="text-xs text-gray-400 mt-1">Supported formats: CSV, and PDF only.</div>
                        </div>

                        <button @click="toggleCard"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                            aria-label="Toggle Card">
                            <Eye v-if="!cardCollapsed" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <div v-if="!cardCollapsed" class="mt-3">
                        <!-- Card content goes here -->
                        <UploadFile :project_id="project.id"/>
                    </div>
                </div>
            </div>
            <div class="grid auto-rows-min min-h-screen gap-4 md:grid-cols-2">
                <div
                    class="relative aspect-video  overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video  overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>

            </div>

        </div>
    </AppLayout>
</template>
