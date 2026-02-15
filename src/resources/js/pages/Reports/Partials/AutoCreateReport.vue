<template>
  <div class="grid h-screen min-h-0 gap-4 md:grid-cols-2">
    <!-- Prompt Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <div class="mb-2 font-semibold text-gray-700">Choose an option to auto-create a dashboard</div>

        <div class="grid grid-cols-1 gap-3">
          <label
            class="group relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 hover:border-gray-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-2 has-[:checked]:ring-indigo-400/40"
          >
            <input
              v-model="selectedTemplateKey"
              type="radio"
              name="dashboard-template"
              value="random-project"
              class="absolute inset-0 appearance-none"
              @change="applyTemplate"
            />

            <div class="flex-1">
              <div class="text-sm font-semibold text-gray-900">Don't want to think</div>
              <div class="mt-1 text-sm text-gray-600">
                Multiple agents will work together to analyze the project. The agents will go through the data see what they can find, normalize the data and then finally create a dashboard. You will have the option to modify the prompt after the dashboard is generated.
              </div>
            </div>

            <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
          </label>

          <label
            class="group relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 hover:border-gray-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-2 has-[:checked]:ring-indigo-400/40"
          >
            <input
              v-model="selectedTemplateKey"
              type="radio"
              name="dashboard-template"
              value="impact-project"
              class="absolute inset-0 appearance-none"
              @change="applyTemplate"
            />

            <div class="flex-1">
              <div class="text-sm font-semibold text-gray-900">Impact measurement (single project)</div>
              <div class="mt-1 text-sm text-gray-600">
                Measures impact for the current project with key outcomes, trends over time, and segment breakdowns.
              </div>
            </div>

            <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
          </label>

          
          
          <label
            class="group relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 hover:border-gray-400 has-[:checked]:border-indigo-500 has-[:checked]:ring-2 has-[:checked]:ring-indigo-400/40"
          >
            <input
              v-model="selectedTemplateKey"
              type="radio"
              name="dashboard-template"
              value="impact-portfolio"
              class="absolute inset-0 appearance-none"
              @change="applyTemplate"
            />

            <div class="flex-1">
              <div class="text-sm font-semibold text-gray-900">Impact measurement (portfolio)</div>
              <div class="mt-1 text-sm text-gray-600">
                Compares impact across projects, highlights top performers, and summarizes overall portfolio impact.
              </div>
            </div>

            <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
          </label>
        </div>
      </div>

      <!-- Model Selection -->
      <div class="flex flex-col min-h-0 mt-3">
        <fieldset>
          <legend class="text-sm/6 font-semibold text-gray-900">Select a model</legend>

          <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
            <label
              v-for="model in modelList"
              :key="model.id"
              :aria-label="model.name"
              class="group relative flex rounded-lg border border-gray-300 bg-white p-4 has-[:disabled]:border-gray-400 has-[:disabled]:bg-gray-200 has-[:disabled]:opacity-25 has-[:checked]:outline has-[:focus-visible]:outline has-[:checked]:outline-2 has-[:focus-visible]:outline-[3px] has-[:checked]:-outline-offset-2 has-[:focus-visible]:-outline-offset-1 has-[:checked]:outline-indigo-600"
            >
              <!-- IMPORTANT: bind selection to model.key (props uses "key") -->
              <input
                v-model="selectedModelKey"
                type="radio"
                name="ai-model"
                :value="model.key"
                class="absolute inset-0 appearance-none focus:outline focus:outline-0"
              />

              <div class="flex-1">
                <span class="block text-sm font-medium text-gray-900">{{ model.name }}</span>
                <span class="mt-1 block text-sm text-gray-500">
                  {{ model.context_window }}
                </span>
              </div>

              <CheckCircle class="invisible size-5 text-indigo-600 group-has-[:checked]:visible" aria-hidden="true" />
            </label>
          </div>
        </fieldset>
      </div>

      <!-- Actions -->
      <div class="w-full bg-white p-4 border-t flex gap-2 z-10 mt-4">
        <button
          class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition flex items-center justify-center"
          @click="testRun"
          :disabled="loading"
        >
          <svg
            v-if="loading"
            class="animate-spin h-5 w-5 mr-2 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>

          <span v-if="loading">Processing...</span>
          <span v-else>Ok, lets go</span>
        </button>

        <!-- <button
          class="flex-1 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300 transition"
          @click="clearPrompt"
        >
          Clear
        </button> -->
      </div>
    </div>

    <!-- Report Preview Column -->
    <div class="relative flex flex-col h-[80vh]">
      <div class="flex-1 flex flex-col min-h-0">
        <label class="mb-2 font-semibold text-gray-700">Dashboard Preview</label>
        <div class="flex-1 rounded border border-gray-200 bg-white p-4 overflow-auto min-h-[200px]" v-html="reportHtml" />
      </div>

      <div class="w-full bg-white p-4 border-t flex items-center gap-2 z-10 mt-4">
        <input
          v-model="reportName"
          type="text"
          placeholder="Dashboard Name"
          class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none"
        />

        <button
          class="flex-1 bg-green-600 text-white py-2 rounded hover:bg-green-700 transition"
          :disabled="!reportHtml || !reportName"
          @click="saveReport"
        >
          Save Dashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { CheckCircle } from 'lucide-vue-next'
import { usePage, useForm } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const projectId = page.props.project.id

const modelList = computed(() => page.props.aiModels ?? [])

// Selected model "key" (not id)
const selectedModelKey = ref('')

const saveGeneratedReport = useForm({
  project_id: projectId,
  is_automatic: 1,
  prompt: '',
  result: '',
  title: '',
  model_key: '', // optional: if you want to store it on save too
})

const LOCAL_TEMPLATE_KEY = (id) => `reportPromptTemplate_${id}`

const prompt = ref('')
const selectedTemplateKey = ref('')
const reportHtml = ref('')
const reportName = ref('')
const loading = ref(false)

onMounted(() => {
  const savedTemplate = localStorage.getItem(LOCAL_TEMPLATE_KEY(projectId))
  if (savedTemplate) {
    selectedTemplateKey.value = savedTemplate
    prompt.value = savedTemplate
  }

  // Default to first model key
  if (!selectedModelKey.value && modelList.value.length) {
    selectedModelKey.value = modelList.value[0].key
  }
})

function applyTemplate() {
  if (!selectedTemplateKey.value) return

  // Keep `prompt` as the selected template id for compatibility with existing backend payloads.
  prompt.value = selectedTemplateKey.value
  localStorage.setItem(LOCAL_TEMPLATE_KEY(projectId), selectedTemplateKey.value)
}

function clearPrompt() {
  prompt.value = ''
  selectedTemplateKey.value = ''
  localStorage.removeItem(LOCAL_TEMPLATE_KEY(projectId))
}

async function testRun() {
  if (!selectedTemplateKey.value) return

  loading.value = true
  try {
    const response = await axios.post(`/projects/${projectId}/autoreports`, {
      // Send template id for auto dashboard generation
      template_id: selectedTemplateKey.value,
      // Keep `prompt` for backwards compatibility if backend still expects it
      prompt: selectedTemplateKey.value,
      model_key: selectedModelKey.value, // <-- sends selected model key to server
      // If your backend expects `key` instead, rename to: key: selectedModelKey.value
    })

    prompt.value = response?.data?.next_agent_prompt ?? ''

    const resultPayload = response?.data?.result

    let html = ''
    if (typeof resultPayload === 'string') {
      html = resultPayload
    } else if (resultPayload && typeof resultPayload === 'object') {
      html = resultPayload.data ?? resultPayload.html ?? ''
    }

    reportHtml.value = html
  } catch (error) {
    // handle error
  } finally {
    loading.value = false
  }
}

function saveReport() {
  saveGeneratedReport.prompt = prompt.value
  saveGeneratedReport.result = reportHtml.value
  saveGeneratedReport.title = reportName.value
  saveGeneratedReport.model_key = selectedModelKey.value
  saveGeneratedReport.is_automatic = 1

  saveGeneratedReport.post(`/projects/${projectId}/sautoreports`, {
    onSuccess: () => {
      // Optionally show a success message or reset form
    },
  })
}
</script>