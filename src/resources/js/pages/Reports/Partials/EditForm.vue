<script setup>
import { ref, onMounted, watch } from 'vue'
import { usePage, useForm } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const projectId = page.props.report.project_id
const report = page.props.report // { id, prompt, result }

const LOCAL_PROMPT_KEY = (projectId, reportId) => `reportPrompt_${projectId}_${reportId}`

const prompt = ref('')
const reportHtml = ref('')
const loading = ref(false)

const saveGeneratedReport = useForm({
  project_id: projectId,
  report_id: report.id,
  prompt: '',
  result: ''
})

// Load initial values from server or localStorage
onMounted(() => {
  const saved = localStorage.getItem(LOCAL_PROMPT_KEY(projectId, report.id))
  prompt.value = saved ?? report.prompt ?? ''
  reportHtml.value = report.result ?? ''
})

// Save prompt to localStorage on change
watch(prompt, (val) => {
  localStorage.setItem(LOCAL_PROMPT_KEY(projectId, report.id), val)
})

function clearPrompt() {
  prompt.value = ''
  localStorage.setItem(LOCAL_PROMPT_KEY(projectId, report.id), '')
}

async function testRun() {
  loading.value = true
  try {
    const response = await axios.post(`/projects/${projectId}/greports`, {
      prompt: prompt.value
    })
    reportHtml.value = response.data.data
  } catch (error) {
    // handle error
  } finally {
    loading.value = false
  }
}

function saveReport() {
  saveGeneratedReport.prompt = prompt.value
  saveGeneratedReport.result = reportHtml.value
  saveGeneratedReport.put(`/reports/${report.id}`, {
    onSuccess: () => {
      // Optionally show a success message or reset form
    }
  })
}
</script>

<template>
   

    
  <div class="grid h-screen min-h-0 gap-4 md:grid-cols-2">
    <!-- Prompt Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <label for="prompt" class="mb-2 font-semibold text-gray-700">Edit prompt and rerun report</label>
        <textarea id="prompt" v-model="prompt"
          class="flex-1 resize-none rounded border border-gray-300 p-3 text-sm focus:border-blue-400 focus:outline-none min-h-[200px]"
          placeholder="Type your prompt here..."></textarea>
      </div>
      <div class="w-full bg-white p-4 border-t flex gap-2 z-10 mt-4">
        <button
          class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition flex items-center justify-center"
          @click="testRun"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span v-if="loading">Processing...</span>
          <span v-else>Test Run</span>
        </button>
        <button class="flex-1 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300 transition"
          @click="clearPrompt">
          Clear
        </button>
      </div>
    </div>

    <!-- Report Preview Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <label class="mb-2 font-semibold text-gray-700">Report Preview</label>
        <div class="flex-1 rounded border border-gray-200 bg-white p-4 overflow-auto min-h-[200px]"
          v-html="reportHtml"></div>
      </div>
      <div class="w-full bg-white p-4 border-t flex z-10 mt-4">
        <button class="flex-1 bg-green-600 text-white py-2 rounded hover:bg-green-700 transition"
          :disabled="!reportHtml" @click="saveReport">
          Save Report
        </button>
      </div>
    </div>
  </div>

</template>