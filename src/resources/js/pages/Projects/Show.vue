<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { ref, onMounted, watch, computed } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import UploadFile from './Partials/UploadFile.vue';
import AddUrl from './Partials/AddUrl.vue';
import FilesList from './Partials/FilesList.vue';
import ActionTabs from './Partials/ActionTabs.vue';
import { useEcho } from '@laravel/echo-vue'
const props = defineProps<{
    project: any
    files: any[]
}>()

const localFiles = ref<any>(props.files);


watch(() => props.files, (newFiles) => {
    localFiles.value = newFiles;
    // console.log(props.files);
    // channelSubscription(localFiles.value);
}, { deep: true });
const breadcrumbs = ref<BreadcrumbItem[]>([
    {
        title: 'Projects',
        href: '/projects',
    },
]);


const subscribedIds = new Set<number | string>()

type ProjectEventDataResponse = {
    project?: any
    files?: any[]
}

async function fetchProjectEventData() {
    const projectId = props.project?.id
    if (!projectId) return

    try {
        const response = await axios.get<ProjectEventDataResponse>(`/projects/${projectId}/event-data`)
        localFiles.value = response?.data?.files ?? []
    } catch (error) {
        console.error('Failed to fetch project event data', error)
    }
}

function userChannelSubscription() {
    useEcho<{ status_message?: string }>(`App.Models.User.${usePage().props.auth.user.id}`, 'CsvStatusUpdate', async (e) => {
        await fetchProjectEventData()
    });
}
userChannelSubscription();

const localFilesArray = computed<any[]>(() => {
  const v = localFiles.value
  if (!v) return []
  if (Array.isArray(v)) return v
  if (v instanceof FileList) return Array.from(v)
  return [v]
})

const cardCollapsed = ref(false);

const LOCAL_KEY = 'dashboardCardCollapsed';

function toggleCard() {
    cardCollapsed.value = !cardCollapsed.value;
    localStorage.setItem(LOCAL_KEY, cardCollapsed.value ? '1' : '0');
}


onMounted(() => {
    // channelSubscription(props.files);
    const stored = localStorage.getItem(LOCAL_KEY);
    cardCollapsed.value = stored === '1' ? true : false;

    breadcrumbs.value.push({
        title: props.project.name,
        href: '/projects/' + props.project.id, // Use Ziggy route helper if available
    });

});

</script>

<template>

    <Head title="File Uploads" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ActionTabs :project_id="props.project.id" />
            <!-- Collapsible Card -->
            <div class="grid auto-rows-min min-h-screen gap-4 md:grid-cols-2">
                
                <div class="mb-2">
                    <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 shadow p-4 transition-all duration-300"
                        :class="cardCollapsed ? 'h-20 overflow-hidden' : ''">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-lg">{{ props.project.name }} -- File Uploads & URL
                                    Entries</div>
                                <div class="text-xs text-gray-400 mt-1">Supported formats: CSV, and PDF only.</div>
                            </div>

                            <!-- <button @click="toggleCard"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                                aria-label="Toggle Card">
                                <Eye v-if="!cardCollapsed" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button> -->
                        </div>
                        <div v-if="!cardCollapsed" class="mt-3">
                            <!-- Card content goes here -->
                            <UploadFile :project_id="props.project.id" />

                            <div class="my-4 text-xs text-gray-400"></div>
                            <AddUrl :project_id="props.project.id" />
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <div
                        class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 shadow p-4 transition-all duration-300">
                        <FilesList :files="localFilesArray" />
                    </div>

                </div>

            </div>



        </div>
    </AppLayout>
</template>