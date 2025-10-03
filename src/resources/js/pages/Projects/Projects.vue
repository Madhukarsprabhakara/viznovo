<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import EmptyState from './Partials/EmptyState.vue';
import ListProjects from './Partials/ListProjects.vue';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: dashboard().url,
    },
    
];



</script>

<template>

    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
           
          <div v-if="$page.props.projects.length ===0" class="mt-35">
                <EmptyState v-if="$page.props.projects.length ===0"/>
               
                   
            </div>
            <div class="mt-12">
                 <ListProjects v-if="$page.props.projects.length >0 " :projects="$page.props.projects"/>
            </div>

        </div>
    </AppLayout>
</template>
