<template>
    <Head title="Create a Project" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen items-start justify-center">
            <div class="w-full max-w-lg  rounded-xl border-1 shadow p-8 mt-12">
                <EditForm />
            </div>
        </div>
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import EditForm from './EditForm.vue';
import { computed } from 'vue';

// ...existing code...
// load project from server props
const page = usePage()
const serverProject = page.props.project ?? null

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Projects', href: '/projects' },
    { title: serverProject.name ?? 'Project', href: serverProject?.id ? `/projects/${serverProject.id}` : '/projects' },
    { title: 'Edit', href: serverProject?.id ? `/projects/${serverProject.id}/edit` : '/projects/create' },
])
// ...existing code...
</script>