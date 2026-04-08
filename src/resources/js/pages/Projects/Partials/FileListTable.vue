<template>
  <div class="px-4 sm:px-6 lg:px-8">
    <!-- <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold text-gray-900">Plans</h1>
        <p class="mt-2 text-sm text-gray-700">Your team is on the <strong class="font-semibold text-gray-900">Startup</strong> plan. The next payment of $80 will be due on August 4, 2022.</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Update credit card</button>
      </div>
    </div> -->
    <div class="-mx-4 mt-10 ring-1 ring-gray-300 sm:mx-0 sm:rounded-lg">
      <table class="relative min-w-full divide-y divide-gray-300">
        <thead>
          <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">File</th>
            <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Type</th>
  
            <th scope="col" class="py-3.5 pl-3 pr-4 sm:pr-6">
              <span class="sr-only">Delete</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(file, fileIdx) in files" :key="file.id">
            <td :class="[fileIdx === 0 ? '' : 'border-t border-transparent', 'relative py-4 pl-4 pr-3 text-sm sm:pl-6']">
              <div class="flex items-center gap-2 font-medium text-gray-900">
                <LoaderCircle
                  v-if="shouldShowImportSpinner(file)"
                  class="h-4 w-4 animate-spin text-sky-600"
                />
                <CircleAlert
                  v-else-if="shouldShowImportFailure(file)"
                  class="h-4 w-4 text-red-600"
                />
                <CheckCircle2
                  v-else-if="shouldShowImportSuccess(file)"
                  class="h-4 w-4 text-green-600"
                />
                {{ file.name }}
              </div>
              
              <div v-if="(file.project_data_logs?.length ?? 0) > 0">
                <span
                  v-if="file.type==='text/csv'"
                  :class="[
                    'text-sm',
                    shouldShowImportFailure(file) ? 'text-red-600' : 'text-green-600',
                  ]"
                >
                  Status: {{ getLatestProjectDataLog(file)?.status_message }}
                </span>
              </div>
              
              <div class="mt-1 flex flex-col text-gray-500 sm:block lg:hidden">
                <span>{{ file.type }}</span>
              </div>
              <div v-if="fileIdx !== 0" class="absolute -top-px left-6 right-0 h-px bg-gray-200"></div>
            </td>
            
            <td :class="[fileIdx === 0 ? '' : 'border-t border-gray-200', 'px-3 py-3.5 text-sm text-gray-500']">
              <div class="sm:hidden"> {{ file.type }} </div>
              <div class="hidden sm:block">{{ file.type  }}</div>
            </td>
            <td :class="[fileIdx === 0 ? '' : 'border-t border-transparent', 'relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6']">
              <button
                type="button"
                class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-red-700 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-white"
                @click="deleteFile(file.id)"
              >
                Delete<span class="sr-only">, {{ file.name }}</span>
              </button>
              <div v-if="fileIdx !== 0" class="absolute -top-px left-0 right-6 h-px bg-gray-200"></div>
            </td>
          </tr>
        </tbody>
      </table>
      <div
        v-if="shouldShowImportNotice()"
        class="border-t border-gray-200 px-4 py-4 sm:px-6"
      >
        <div class="flex items-start gap-3">
          <CheckCircle2
            v-if="hasAnySuccessfulCsvImport()"
            class="mt-0.5 h-5 w-5 shrink-0 text-green-600"
          />
          <p :class="['text-sm leading-6', hasAnySuccessfulCsvImport() ? 'text-green-600' : 'text-sky-900']">
            {{ hasAnySuccessfulCsvImport()
              ? 'You can now start creating reports using the Intelligent Reports tab on the top.'
              : 'Please wait for the files to be imported before beginning to create your report through the Intelligent Reports tab.' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CheckCircle2, CircleAlert, LoaderCircle } from 'lucide-vue-next';

type ProjectDataLog = {
  id?: number | string | null,
  status_message?: string | null,
  is_imported?: number | string | null,
}

type ProjectFile = {
  id: number | string,
  name: string,
  url: string,
  type?: string | null,
  project_data_logs?: ProjectDataLog[],
}

const props = defineProps<{
  files: ProjectFile[]
}>();

const form = useForm({});

const getLatestProjectDataLog = (file: ProjectFile) => {
  const logs = file.project_data_logs ?? [];

  if (logs.length === 0) {
    return null;
  }

  return logs.reduce((latestLog, currentLog) => {
    const latestId = Number(latestLog.id ?? 0);
    const currentId = Number(currentLog.id ?? 0);

    return currentId >= latestId ? currentLog : latestLog;
  });
};

const shouldShowImportSpinner = (file: ProjectFile) => {
  if (file.type !== 'text/csv') {
    return false;
  }

  const latestLog = getLatestProjectDataLog(file);

  return latestLog !== null
    && Number(latestLog.is_imported ?? 0) !== 1
    && latestLog.status_message !== 'Import failed';
};

const shouldShowImportFailure = (file: ProjectFile) => {
  if (file.type !== 'text/csv') {
    return false;
  }

  const latestLog = getLatestProjectDataLog(file);

  return latestLog?.status_message === 'Import failed';
};

const shouldShowImportSuccess = (file: ProjectFile) => {
  if (file.type !== 'text/csv') {
    return false;
  }

  const latestLog = getLatestProjectDataLog(file);

  return latestLog !== null && Number(latestLog.is_imported ?? 0) === 1;
};

const getCsvFiles = () => props.files.filter((file) => file.type === 'text/csv');

const hasAnySuccessfulCsvImport = () => getCsvFiles().some((file) => shouldShowImportSuccess(file));

const shouldShowImportNotice = () => getCsvFiles().length > 0;

const deleteFileFinally = (fileId: number | string) => {
  form.delete('/projectdata/' + fileId, {
    errorBag: 'deleteProjectFile',
  });
};

function deleteFile(fileId: number | string) {
  if (confirm('Are you sure you want to delete this file?')) {
    deleteFileFinally(fileId);
  }
}
</script>