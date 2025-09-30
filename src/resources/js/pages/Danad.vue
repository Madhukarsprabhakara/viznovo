<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Report1 from './Report1.vue';
import Report2 from './Report2.vue';
// import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Designer Reports',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Report 1 -->
                <div class="flex-1 bg-white rounded-xl shadow p-4">
                    <div class="mb-2">
                        <span class="inline-block px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                           GPT-5
                        </span>
                    </div>
                    <!-- Report 1 content -->
                    
                    <div>
                        <!-- Place your HTML report here -->
                        <Report1 />
                    </div>
                </div>
                <!-- Report 2 -->
                <div class="flex-1 bg-white rounded-xl shadow p-4">
                    <div class="mb-2">
                        <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                            Llama-3.3-70B-Instruct
                        </span>
                    </div>
                    <!-- Report 2 content -->
                    
                    <div>
                        <!-- Place your HTML report here -->
                         <Report2 />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
