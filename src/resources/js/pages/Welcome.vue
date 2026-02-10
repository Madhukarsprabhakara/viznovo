<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { login, register, home } from '@/routes'
import { FileText, FileSpreadsheet, Sparkles, BarChart3, AlertCircle, CheckCircle, ExternalLink } from 'lucide-vue-next'
import AppLogoIcon from '@/components/AppLogoIcon.vue'


</script>

<template>
  <Head title="viznovo — Impact Measurement on autopilot using agents" />
  <div class="min-h-screen flex flex-col bg-white text-slate-900">
    <!-- Top Navigation -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <nav class="flex items-center justify-between">
          <Link :href="home()" class="flex items-center gap-2 font-medium">
            <div class="flex h-9 w-9 items-center justify-center rounded-md">
              <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" />
            </div>
            <span class="text-md font-semibold text-slate-900 leading-none">Viznovo</span>
          </Link>
          <div class="flex items-center gap-4">
            <Link :href="login()" class="text-sm text-slate-700 hover:text-indigo-600">Log in</Link>
          </div>
        </nav>
      </div>
    </div>

    <main class="flex-1 flex flex-col justify-center">
      <!-- Hero -->
      <header class="bg-gradient-to-b from-white to-slate-50">
        <div class="max-w-7xl mx-auto px-6 py-8 lg:py-12">
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-center">
            <!-- Main message (1/4) -->
            <div class="lg:col-span-1">
              <h2 class="text-2xl font-semibold text-slate-700">
                Open-source impact measurement app that turns data into insights and insights into presentable dashboards in minutes.
              </h2>
            </div>

            <!-- Animation (3/4) -->
            <div class="lg:col-span-3 w-full overflow-hidden">
              <div class="flex justify-center">
                <div class="vzn-pipeline vzn-pipeline-h flex items-center gap-2 origin-center scale-[0.92] xl:scale-100" aria-label="Files are processed by three AI agents: discovery, cleanup, and insights; the third agent produces a designer-quality dashboard.">
              <!-- Source files -->
              <div class="flex flex-col gap-2 w-56 shrink-0">
                <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition vzn-float overflow-hidden">
                  <div class="flex items-center gap-3">
                    <FileText class="w-7 h-7 text-red-500 flex-shrink-0" />
                    <div class="min-w-0">
                      <div class="font-semibold text-sm truncate">Financial Report.pdf</div>
                      <div class="text-xs text-slate-500 truncate">Quarterly financials</div>
                    </div>
                  </div>
                </div>

                <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition vzn-float overflow-hidden">
                  <div class="flex items-center gap-3">
                    <FileText class="w-7 h-7 text-indigo-600 flex-shrink-0" />
                    <div class="min-w-0">
                      <div class="font-semibold text-sm truncate">Interview Transcript.txt</div>
                      <div class="text-xs text-slate-500 truncate">Founder interview notes</div>
                    </div>
                  </div>
                </div>

                <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition vzn-float vzn-float-delay overflow-hidden">
                  <div class="flex items-center gap-3">
                    <FileSpreadsheet class="w-7 h-7 text-green-600 flex-shrink-0" />
                    <div class="min-w-0">
                      <div class="font-semibold text-sm truncate">Revenue_Data.csv</div>
                      <div class="text-xs text-slate-500 truncate">Monthly revenue breakdown</div>
                    </div>
                  </div>
                </div>
                <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition vzn-float vzn-float-delay overflow-hidden">
                  <div class="flex items-center gap-3">
                    <FileSpreadsheet class="w-7 h-7 text-green-600 flex-shrink-0" />
                    <div class="min-w-0">
                      <div class="font-semibold text-sm truncate">Impact Data.csv</div>
                      <div class="text-xs text-slate-500 truncate">Survey data from participants</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Connector: Files -> Agents -->
              <div class="vzn-connector" aria-hidden="true">
                <div class="h-px w-full bg-slate-200"></div>
                <span class="vzn-dot vzn-dot-1 bg-indigo-600"></span>
              </div>

              <!-- Agents stack (vertical) -->
              <div class="flex flex-col gap-2 w-44 shrink-0">
                <!-- Agent 1 -->
                <div class="relative rounded-lg border border-slate-200 bg-white p-3 shadow-sm overflow-hidden">
                  <div class="vzn-agent-glow vzn-agent-glow-1 absolute inset-0 bg-indigo-50 opacity-0" aria-hidden="true"></div>
                  <div class="relative">
                    <div class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 whitespace-nowrap">
                      Agent 1
                    </div>
                    <div class="mt-2 flex items-start gap-2">
                      <Sparkles class="w-4 h-4 text-indigo-600 flex-shrink-0" />
                      <div class="min-w-0">
                        <div class="font-semibold text-sm text-slate-900 leading-snug">Discovery</div>
                        <div class="mt-0.5 text-[11px] text-slate-600 leading-snug">Understands each file</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Agent 2 -->
                <div class="relative rounded-lg border border-slate-200 bg-white p-3 shadow-sm overflow-hidden">
                  <div class="vzn-agent-glow vzn-agent-glow-2 absolute inset-0 bg-indigo-50 opacity-0" aria-hidden="true"></div>
                  <div class="relative">
                    <div class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 whitespace-nowrap">
                      Agent 2
                    </div>
                    <div class="mt-2 flex items-start gap-2">
                      <Sparkles class="w-4 h-4 text-indigo-600 flex-shrink-0" />
                      <div class="min-w-0">
                        <div class="font-semibold text-sm text-slate-900 leading-snug">Cleanup</div>
                        <div class="mt-0.5 text-[11px] text-slate-600 leading-snug">Normalises the data</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Agent 3 -->
                <div class="relative rounded-lg border border-slate-200 bg-white p-3 shadow-sm overflow-hidden">
                  <div class="vzn-agent-glow vzn-agent-glow-3 absolute inset-0 bg-indigo-50 opacity-0" aria-hidden="true"></div>
                  <div class="relative">
                    <div class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 whitespace-nowrap">
                      Agent 3
                    </div>
                    <div class="mt-2 flex items-start gap-2">
                      <Sparkles class="w-4 h-4 text-indigo-600 flex-shrink-0" />
                      <div class="min-w-0">
                        <div class="font-semibold text-sm text-slate-900 leading-snug">Insights</div>
                        <div class="mt-0.5 text-[11px] text-slate-600 leading-snug">Builds the dashboard</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Connector: Agents -> Dashboard -->
              <div class="vzn-connector" aria-hidden="true">
                <div class="h-px w-full bg-slate-200"></div>
                <span class="vzn-dot vzn-dot-4 bg-indigo-600"></span>
              </div>

              <!-- Dashboard output (output of Agent 3) -->
              <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-4 shadow-2xl vzn-dashboard w-96 shrink-0">
                <div class="flex items-center gap-2 mb-3">
                  <BarChart3 class="w-5 h-5 text-white" />
                  <div class="text-white font-semibold text-sm">Designer-quality Dashboard</div>
                </div>

                <div class="bg-white/10 backdrop-blur rounded-lg p-3 space-y-3">
                  <div class="flex items-end gap-2 h-20">
                    <div class="bg-white/80 rounded-t w-full" style="height: 45%"></div>
                    <div class="bg-white/80 rounded-t w-full" style="height: 70%"></div>
                    <div class="bg-white/80 rounded-t w-full" style="height: 85%"></div>
                    <div class="bg-white/80 rounded-t w-full" style="height: 60%"></div>
                  </div>

                  <div class="grid grid-cols-3 gap-2">
                    <div class="bg-white/20 backdrop-blur rounded p-2">
                      <div class="text-xs text-white/70">Revenue</div>
                      <div class="text-sm font-bold text-white">$2.4M</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded p-2">
                      <div class="text-xs text-white/70">Reach</div>
                      <div class="text-sm font-bold text-white">+23%</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded p-2">
                      <div class="text-xs text-white/70">Impact</div>
                      <div class="text-sm font-bold text-white">34%</div>
                    </div>
                  </div>
                </div>
              </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Private beta -->
      <section class="bg-white">
        <div class="max-w-7xl mx-auto px-6 py-8">
          <div class="flex flex-col items-center justify-center gap-4 text-center">
            <p class="text-slate-700">
              We are in private beta at the moment. If you already have an email invite, respond to that email. If you don't have an invite but would like to join, please fill out the form linked below and we'll be in touch. And while you are here don't forget to download the Impact Measurement in the Age of AI book!
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
              <a
                href="https://docs.google.com/forms/d/e/1FAIpQLSf9YpuofIBY6C53vVstDeoS0mO1GhlUIDaVCXK5NL_nrAKudQ/viewform?usp=publish-editor"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 font-medium"
              >
                Get in touch
              </a>

              <a
                href="https://madhukarsp.gumroad.com/l/immbook"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-md border border-slate-300 bg-white text-slate-900 hover:bg-slate-50 font-medium"
              >
                <ExternalLink class="w-4 h-4 text-slate-700" aria-hidden="true" />
                Download Impact Measurement Book
              </a>
            </div>
          </div>
        </div>
      </section>
    </main>

    

    <!-- Footer -->
    <!-- <footer class="border-t bg-white">
      <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-sm text-slate-600">© {{ new Date().getFullYear() }} Viznovo.</div>
        <nav class="flex gap-6">
          <a href="/privacy" class="text-sm text-slate-600 hover:text-indigo-600">Privacy</a>
          <a href="/terms" class="text-sm text-slate-600 hover:text-indigo-600">Terms</a>
          <a href="/contact" class="text-sm text-slate-600 hover:text-indigo-600">Contact</a>
        </nav>
      </div>
    </footer> -->
  </div>
</template>

<style scoped>
.vzn-pipeline {
  --vzn-dur: 7s;
}

.vzn-pipeline-h {
  --vzn-hop: 18px;
}

.vzn-connector {
  position: relative;
  display: flex;
  align-items: center;
  width: var(--vzn-hop);
  min-width: var(--vzn-hop);
  height: 16px;
}

.vzn-dot {
  position: absolute;
  left: -4px;
  top: 50%;
  margin-top: -4px;
  width: 8px;
  height: 8px;
  border-radius: 9999px;
  opacity: 0;
  animation: vzn-travel var(--vzn-dur) linear infinite;
}

.vzn-dot-1 { animation-delay: 0.1s; }
.vzn-dot-2 { animation-delay: 999s; }
.vzn-dot-3 { animation-delay: 999s; }
.vzn-dot-4 { animation-delay: 1.9s; }

.vzn-agent-glow {
  animation: vzn-glow var(--vzn-dur) ease-in-out infinite;
}

.vzn-agent-glow-1 { animation-delay: 0.2s; }
.vzn-agent-glow-2 { animation-delay: 1.45s; }
.vzn-agent-glow-3 { animation-delay: 2.7s; }

.vzn-dashboard {
  animation: vzn-dashboard var(--vzn-dur) ease-in-out infinite;
}

.vzn-float {
  animation: vzn-float 4.2s ease-in-out infinite;
}

.vzn-float-delay {
  animation-delay: 0.6s;
}

@keyframes vzn-travel {
  0% { transform: translateX(0); opacity: 0; }
  8% { opacity: 1; }
  55% { transform: translateX(var(--vzn-hop)); opacity: 1; }
  70% { opacity: 0; }
  100% { transform: translateX(var(--vzn-hop)); opacity: 0; }
}

@keyframes vzn-glow {
  0%, 20% { opacity: 0; }
  30%, 42% { opacity: 0.65; }
  55%, 100% { opacity: 0; }
}

@keyframes vzn-dashboard {
  0%, 55% { transform: translateY(8px); opacity: 0.8; }
  68%, 85% { transform: translateY(0); opacity: 1; }
  100% { transform: translateY(8px); opacity: 0.8; }
}

@keyframes vzn-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

@media (prefers-reduced-motion: reduce) {
  .vzn-dot,
  .vzn-agent-glow,
  .vzn-dashboard,
  .vzn-float {
    animation: none !important;
  }
}
</style>