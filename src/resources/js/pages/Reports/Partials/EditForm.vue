<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { usePage, useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import DynamicNotification from '@/components/DynamicNotification.vue'
import { CheckCircle, Download, ExternalLink, Info } from 'lucide-vue-next'
import { useEcho } from '@laravel/echo-vue'

const page = usePage()
const projectId = page.props.report.project_id
const report = page.props.report // { id, prompt, result, model_key? }
const modelList = page.props.aiModels ?? []

const LOCAL_PROMPT_KEY = (projectId, reportId) => `reportPrompt_${projectId}_${reportId}`
const LOCAL_NOTIFICATION_KEY = (reportId) => `reportLogsNotificationVisible_${reportId}`

const title = ref('')
const prompt = ref('')
const reportHtml = ref('')
const loading = ref(false)
const errorMessage = ref('')
const isNotificationOpen = ref(true)

// NEW: selected model "key" (not id)
const selectedModelKey = ref('')

const saveGeneratedReport = useForm({
  project_id: projectId,
  report_id: report.id,
  title: '',
  prompt: '',
  result: '',
  model_key: '' // optional: include if you persist it on update
})

const hasReportName = computed(() => Boolean(String(title.value ?? '').trim()))
const hasPromptInstructions = computed(() => Boolean(String(prompt.value ?? '').trim()))
const hasSelectedModel = computed(() => Boolean(String(selectedModelKey.value ?? '').trim()))
const hasReportPreview = computed(() => Boolean(String(reportHtml.value ?? '').trim()))

const hasRequiredReportInputs = computed(() => {
  return hasReportName.value && hasPromptInstructions.value && hasSelectedModel.value
})

const canReanalyzeAndSave = computed(() => {
  return hasRequiredReportInputs.value
})

const canSaveWithoutAnalysis = computed(() => {
  return hasRequiredReportInputs.value && hasReportPreview.value
})

const reportLogs = computed(() => {
  return Array.isArray(report?.report_logs) ? report.report_logs : []
})

function openNotification() {
  isNotificationOpen.value = true
  localStorage.setItem(LOCAL_NOTIFICATION_KEY(report.id), '1')
}

function closeNotification() {
  isNotificationOpen.value = false
  localStorage.setItem(LOCAL_NOTIFICATION_KEY(report.id), '0')
}

function userChannelSubscription() {
  useEcho(`App.Models.Report.${report.id}`, 'ReportStatusUpdate', async (e) => {
    // await fetchProjectEventData()
    console.log('Received ReportStatusUpdate event for report')
    router.visit(window.location.href)
  });
}
userChannelSubscription();
// Load initial values from server or localStorage
onMounted(() => {
  const saved = localStorage.getItem(LOCAL_PROMPT_KEY(projectId, report.id))
  const savedNotificationState = localStorage.getItem(LOCAL_NOTIFICATION_KEY(report.id))
  title.value = report.title ?? ''
  prompt.value = saved ?? report.prompt ?? ''
  reportHtml.value = report.result ?? ''
  isNotificationOpen.value = savedNotificationState === null ? true : savedNotificationState === '1'

  // Default model selection:
  // 1) use report.model_key if provided by backend
  // 2) else fallback to first model in list
  selectedModelKey.value = report.model_key ?? selectedModelKey.value
  if (!selectedModelKey.value && modelList.length) {
    selectedModelKey.value = modelList[0].key
  }
})

// Save prompt to localStorage on change
watch(prompt, (val) => {
  localStorage.setItem(LOCAL_PROMPT_KEY(projectId, report.id), val)
})

async function reanalyzeAndSaveReport() {
  if (loading.value || !canReanalyzeAndSave.value) return

  openNotification()
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.post(`/projects/${projectId}/greports`, {
      report_id: report.id,
      title: title.value,
      prompt: prompt.value,
      model_key: selectedModelKey.value // <-- send selected model key
      // If your backend expects `key` instead, rename to: key: selectedModelKey.value
    })
    reportHtml.value = response.data.data
  } catch (error) {
    const responseData = error?.response?.data
    errorMessage.value = responseData?.message ?? error?.message ?? 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}

function saveReportWithoutAnalysis() {
  if (loading.value || !canSaveWithoutAnalysis.value) return

  saveGeneratedReport.title = title.value
  saveGeneratedReport.prompt = prompt.value
  saveGeneratedReport.result = reportHtml.value
  saveGeneratedReport.model_key = selectedModelKey.value

  saveGeneratedReport.put(`/reports/${report.id}`, {
    onSuccess: () => {
      // Optionally show a success message or reset form
    }
  })
}
</script>

<template>
  <div class="grid h-screen min-h-0 mt-4 gap-4 md:grid-cols-2">
    <!-- Prompt Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <label for="prompt" class="mb-2 font-semibold text-gray-700">
          Edit prompt to reanalyze data and generate a report
        </label>

        <textarea id="prompt" v-model="prompt"
          class="flex-1 resize-none rounded border border-gray-300 p-3 text-sm focus:border-blue-400 focus:outline-none min-h-[200px]"
          placeholder="Type your prompt here..." />
      </div>

      <!-- Model Selection -->
      <div class="flex flex-col min-h-0 mt-3">
        <fieldset>
          <legend class="text-sm/6 font-semibold text-gray-900">Select a model</legend>

          <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
            <label v-for="model in modelList" :key="model.id" :aria-label="model.name"
              class="group relative flex rounded-lg border border-gray-300 bg-white p-4 has-[:disabled]:border-gray-400 has-[:disabled]:bg-gray-200 has-[:disabled]:opacity-25 has-[:checked]:outline has-[:focus-visible]:outline has-[:checked]:outline-2 has-[:focus-visible]:outline-[3px] has-[:checked]:-outline-offset-2 has-[:focus-visible]:-outline-offset-1 has-[:checked]:outline-indigo-600">
              <input v-model="selectedModelKey" type="radio" name="ai-model" :value="model.key"
                class="absolute inset-0 appearance-none focus:outline focus:outline-0" />

              <div class="flex-1">
                <span class="block text-sm font-medium text-gray-900">{{ model.name }}</span>
                <span class="mt-1 block text-sm text-gray-500">{{ model.context_window }}</span>
              </div>

              <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
            </label>
          </div>
        </fieldset>
      </div>

      <!-- Actions -->
      <div v-if="errorMessage"
        class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800 whitespace-pre-wrap">
        {{ errorMessage }}
      </div>

      <div class="w-full bg-white p-4 flex gap-2 z-10 mt-4">
        <input v-model="title" type="text" placeholder="Report Name"
          class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none" />

        <button
          class="ml-3 inline-flex flex-1 items-center justify-center gap-2 rounded-md border border-transparent bg-gradient-to-r from-indigo-500 via-indigo-400 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:from-indigo-400 hover:via-indigo-500 hover:to-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:border-gray-300 disabled:bg-none disabled:bg-gray-100 disabled:from-gray-100 disabled:via-gray-100 disabled:to-gray-100 disabled:text-gray-500 disabled:shadow-none disabled:hover:from-gray-100 disabled:hover:via-gray-100 disabled:hover:to-gray-100"
          @click="reanalyzeAndSaveReport" :disabled="loading || !canReanalyzeAndSave">
          <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>

          <span v-if="loading">Processing...</span>
          <span v-else>Reanalyze and save</span>
        </button>

        <button class="flex-1 rounded border border-transparent bg-emerald-400 py-2 text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:border-gray-300 disabled:bg-gray-100 disabled:text-gray-500 disabled:hover:bg-gray-100"
          @click="saveReportWithoutAnalysis" :disabled="loading || !canSaveWithoutAnalysis">
          Save name only
        </button>
      </div>
    </div>

    <!-- Report Preview Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <div class="mb-2 flex items-center gap-2">
          <label class="font-semibold text-gray-700">Report preview</label>
          <button type="button"
            class="inline-flex items-center rounded-md p-1 text-yellow-600 hover:bg-yellow-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
            title="View report logs" aria-label="View report logs" @click="openNotification">
            <Info class="size-4" aria-hidden="true" />
          </button>
          <a
            :href="`/reports/${report.uuid}/pdf`"
            class="inline-flex items-center rounded-md p-1 text-slate-600 hover:bg-slate-100 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
            :title="`Download report ${report.title} as PDF`"
          >
            <Download class="size-4" aria-hidden="true" />
          </a>
          <a
            :href="`/reports/${report.uuid}`"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center rounded-md p-1 text-orange-700 hover:bg-blue-50 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500"
            :title="`Open public report /reports/${report.uuid}`"
          >
            <ExternalLink class="size-4" aria-hidden="true" />
          </a>
        </div>
        <div class="flex-1 rounded border border-gray-200 bg-white p-4 overflow-auto min-h-[200px]"
          v-html="reportHtml" />
      </div>
    </div>

    <DynamicNotification :show="isNotificationOpen" :title="`${report.title}`"
      :report-uuid="report.uuid"
      description="Updates from agents will appear here as data is analyzed." :logs="reportLogs" @close="closeNotification" />
  </div>
</template>