<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { ref, onMounted, defineProps } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import CreateReport from './Partials/CreateReport.vue';
import ReportsList from './Partials/ReportsList.vue';
import ActionTabs from '../Projects/Partials/ActionTabs.vue';
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

    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <ActionTabs :project_id="project.id"/>
            <!-- Collapsible Card -->
            
            <CreateReport v-if="$page.props.reports.length==0" />
            <ReportsList v-else />

        </div>
    </AppLayout>
</template>
