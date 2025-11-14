<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { login, register, dashboard } from '@/routes'
import { FileText, FileSpreadsheet, Sparkles, BarChart3 } from 'lucide-vue-next'
</script>

<template>
  <Head title="viznovo — Dashboards from PDFs & CSVs in Minutes" />
  <div class="min-h-screen bg-gradient-to-b from-white via-slate-50 to-slate-100 text-slate-900">
    <!-- Top Navigation -->
    <div class="max-w-7xl mx-auto px-6 py-4">
      <nav class="flex items-center justify-between">
        <div class="text-xl font-bold text-indigo-600">viznovo</div>
        <div class="flex items-center gap-4">
          <Link :href="login()" class="text-sm text-slate-700 hover:text-indigo-600">Log in</Link>
        </div>
      </nav>
    </div>

    <!-- Hero -->
    <header class="max-w-7xl mx-auto px-6 py-8 lg:py-12">
      <div class="flex flex-col lg:flex-row items-start gap-8">
        <div class="flex-1">
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight">
            <span class="text-yellow-600">Analyze</span> PDFs & CSVs.<br />
            Create stunning dashboards<br />
            <span class="text-indigo-600">in minutes, using plain English.</span>
          </h1>
          <p class="mt-6 text-lg text-slate-600 max-w-2xl">
            Upload your data — financials, PDFs, CSVs — describe what you need in plain English, 
            and get all the analysis on stunning designer quality dashboards. Instantly. No coding. No complex tools.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="https://viznovo.com/reports/518b8a2e-a752-464e-85a0-d11e3e719de3" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md shadow-lg hover:bg-indigo-700 text-lg font-medium">
              Example dashboard
            </a>
            <a href="#pricing" class="inline-flex items-center px-6 py-3 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 text-lg">
              See pricing
            </a>
          </div>

          <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
              <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">stunning dashboards</div>
              <!-- <div class="mt-2 font-medium text-slate-900">Create as many as you need</div> -->
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
              <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Plain English</div>
              <!-- <div class="mt-2 font-medium text-slate-900">No SQL, no coding, no formulas</div> -->
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
              <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Minutes, not hours</div>
              <!-- <div class="mt-2 font-medium text-slate-900">Generate stunning dashboards instantly</div> -->
            </div>
          </div>
        </div>

        <!-- Visual: CSV/PDF → Dashboard (aligned to top) -->
        <div class="w-full sm:w-[420px] lg:w-[480px] flex-shrink-0">
          <div class="relative">
            <!-- Source files -->
            <div class="flex flex-col gap-3">
              <!-- PDF file -->
              <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition">
                <div class="flex items-center gap-3">
                  <FileText class="w-8 h-8 text-red-500 flex-shrink-0" />
                  <div>
                    <div class="font-semibold text-sm">Financial_Report.pdf</div>
                    <div class="text-xs text-slate-500">Quarterly financials</div>
                  </div>
                </div>
              </div>

              <!-- CSV file -->
              <div class="bg-white border-2 border-slate-200 rounded-lg p-3 shadow-md hover:shadow-lg transition">
                <div class="flex items-center gap-3">
                  <FileSpreadsheet class="w-8 h-8 text-green-600 flex-shrink-0" />
                  <div>
                    <div class="font-semibold text-sm">Revenue_Data.csv</div>
                    <div class="text-xs text-slate-500">Monthly revenue breakdown</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- AI Processing indicator with gradient glow -->
            <div class="flex items-center justify-center my-5">
              <div class="relative">
                <!-- Glowing background -->
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full blur-lg opacity-60 animate-pulse"></div>
                
                <!-- Main container -->
                <div class="relative flex items-center gap-3 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full shadow-lg">
                  <Sparkles class="w-5 h-5 text-white animate-spin" style="animation-duration: 2s;" />
                  <span class="text-sm font-semibold text-white">AI Processing</span>
                  <Sparkles class="w-5 h-5 text-white animate-spin" style="animation-duration: 2s; animation-direction: reverse;" />
                </div>
              </div>
            </div>

            <!-- Dashboard output -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-5 shadow-2xl">
              <div class="flex items-center gap-2 mb-3">
                <BarChart3 class="w-5 h-5 text-white" />
                <div class="text-white font-semibold text-sm">Interactive Dashboard</div>
              </div>

              <!-- Mock dashboard content -->
              <div class="bg-white/10 backdrop-blur rounded-lg p-3 space-y-3">
                <!-- Chart bars -->
                <div class="flex items-end gap-2 h-20">
                  <div class="bg-white/80 rounded-t w-full" style="height: 45%"></div>
                  <div class="bg-white/80 rounded-t w-full" style="height: 70%"></div>
                  <div class="bg-white/80 rounded-t w-full" style="height: 85%"></div>
                  <div class="bg-white/80 rounded-t w-full" style="height: 60%"></div>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-3 gap-2">
                  <div class="bg-white/20 backdrop-blur rounded p-2">
                    <div class="text-xs text-white/70">Revenue</div>
                    <div class="text-sm font-bold text-white">$2.4M</div>
                  </div>
                  <div class="bg-white/20 backdrop-blur rounded p-2">
                    <div class="text-xs text-white/70">Growth</div>
                    <div class="text-sm font-bold text-white">+23%</div>
                  </div>
                  <div class="bg-white/20 backdrop-blur rounded p-2">
                    <div class="text-xs text-white/70">Margin</div>
                    <div class="text-sm font-bold text-white">34%</div>
                  </div>
                </div>
              </div>

              <div class="mt-3 text-xs text-white/80 text-center">
                ✨ Generated in minutes
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Features -->
    <section class="max-w-7xl mx-auto px-6 py-16 bg-white rounded-xl shadow-sm">
      <h2 class="text-3xl font-bold text-center">How it works</h2>
      <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">1</div>
          <h3 class="mt-4 text-lg font-semibold">Upload your data</h3>
          <p class="mt-2 text-sm text-slate-600">CSV files, PDFs, or website URLs — we extract tables and text automatically.</p>
        </div>
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">2</div>
          <h3 class="mt-4 text-lg font-semibold">Describe in plain English</h3>
          <p class="mt-2 text-sm text-slate-600">No SQL, no formulas — just tell us what insights or charts you need.</p>
        </div>
        <div class="text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">3</div>
          <h3 class="mt-4 text-lg font-semibold">Get your dashboard</h3>
          <p class="mt-2 text-sm text-slate-600">Interactive HTML dashboards ready in minutes. Edit, share, or export as needed.</p>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="max-w-7xl mx-auto px-6 py-16">
      <div class="text-center mb-10">
        <h2 class="text-3xl font-bold">Simple, transparent pricing</h2>
        <p class="mt-3 text-slate-600">One plan. Everything included.</p>
      </div>

      <div class="max-w-lg mx-auto bg-white border border-slate-200 rounded-xl shadow-lg p-8">
        <div class="text-sm font-semibold text-indigo-600 uppercase tracking-wide">Pro Plan</div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-5xl font-bold">$2,000</span>
          <span class="text-lg text-slate-600">/month</span>
        </div>
        <div class="text-sm text-slate-500 mt-1">Billed annually ($24,000/year)</div>

        <ul class="mt-8 space-y-3 text-sm text-slate-700">
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Unlimited dashboards</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Unlimited teammates</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Unlimited projects</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Priority support & onboarding</span>
          </li>
          <li class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Secure private storage</span>
          </li>
        </ul>

        <div class="mt-8">
          <a href="#" class="block w-full text-center px-6 py-3 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700">
            Talk to us
          </a>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-white mt-16">
      <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-sm text-slate-600">© {{ new Date().getFullYear() }} viznovo. Built for investment teams.</div>
        <nav class="flex gap-6">
          <a href="/privacy" class="text-sm text-slate-600 hover:text-indigo-600">Privacy</a>
          <a href="/terms" class="text-sm text-slate-600 hover:text-indigo-600">Terms</a>
          <a href="/contact" class="text-sm text-slate-600 hover:text-indigo-600">Contact</a>
        </nav>
      </div>
    </footer>
  </div>
</template>