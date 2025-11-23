<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { login, register, dashboard } from '@/routes'
import { FileText, FileSpreadsheet, Sparkles, BarChart3, AlertCircle, CheckCircle, ExternalLink } from 'lucide-vue-next'
</script>

<template>
  <Head title="viznovo — Your AI Analyst for Investment Teams" />
  <div class="min-h-screen bg-white text-slate-900">
    <!-- Top Navigation -->
    <div class="bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <nav class="flex items-center justify-between">
          <div class="text-xl font-bold text-indigo-600">viznovo</div>
          <div class="flex items-center gap-4">
            <Link :href="login()" class="text-sm text-slate-700 hover:text-indigo-600">Log in</Link>
          </div>
        </nav>
      </div>
    </div>

    <!-- Hero -->
    <header class="bg-gradient-to-b from-white to-slate-50">
      <div class="max-w-7xl mx-auto px-6 py-8 lg:py-12">
        <div class="flex flex-col lg:flex-row items-start gap-8">
          <div class="flex-1">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight">
              Your AI Analyst.<br />
              <span class="text-indigo-600">For Investment Teams.</span>
            </h1>
            <p class="mt-6 text-lg text-slate-600 max-w-2xl">
              Upload PDF reports, CSVs, or data files — and get analyst-grade dashboards, insights, KPIs, and IC memos <span class="text-yellow-600 font-semibold">in minutes</span>, <span class="text-green-600 font-semibold">using plain English</span>. Purpose-built for private equity, venture capital, and institutional and impact funds.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
              <a href="https://viznovo.com/reports/518b8a2e-a752-464e-85a0-d11e3e719de3" target="_blank" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-md shadow-lg hover:bg-indigo-700 text-lg font-medium">
                <span class="mr-2">Explore sample dashboard</span> <ExternalLink class="w-5 h-5" />
              </a>
              <a href="#pricing" class="inline-flex items-center px-6 py-3 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50 text-lg">
                See pricing
              </a>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Secure, fund-grade infrastructure</div>
              </div>
              <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">IC-ready insights</div>
              </div>
              <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Designer quality dashboards</div>
              </div>
              <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Built for teams managing £50M–£5B AUM</div>
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
      </div>
    </header>

    <!-- Problem Section -->
    <section class="bg-gradient-to-b from-red-50 to-white">
      <div class="max-w-7xl mx-auto px-6 py-16">
        <!-- Chip Label -->
        <div class="flex justify-center mb-6">
          <span class="inline-flex items-center px-4 py-1.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full border border-red-200">
            The Problem
          </span>
        </div>

        <div class="text-center mb-10">
          <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
            Your Team Is Drowning in PDFs,<br />
            CSVs, and Annual Reports
          </h2>
          <p class="mt-4 text-lg text-slate-600">
            Funds spend <span class="font-semibold text-slate-900">40–60% of analyst time manually:</span>
          </p>
        </div>

        <div class="max-w-4xl mx-auto space-y-6 mb-12">
          <!-- First Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-red-200 rounded-lg p-6 shadow-sm hover:shadow-md transition">
              <div class="flex items-start gap-3">
                <AlertCircle class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-slate-900 text-sm">Extracting KPIs from 30–100 page PDFs</h3>
                </div>
              </div>
            </div>

            <div class="bg-white border border-red-200 rounded-lg p-6 shadow-sm hover:shadow-md transition">
              <div class="flex items-start gap-3">
                <AlertCircle class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-slate-900 text-sm">Reconciling numbers across multiple documents</h3>
                </div>
              </div>
            </div>
          </div>

          <!-- Second Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-red-200 rounded-lg p-6 shadow-sm hover:shadow-md transition">
              <div class="flex items-start gap-3">
                <AlertCircle class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-slate-900 text-sm">Building dashboards and tables</h3>
                </div>
              </div>
            </div>

            <div class="bg-white border border-red-200 rounded-lg p-6 shadow-sm hover:shadow-md transition">
              <div class="flex items-start gap-3">
                <AlertCircle class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-slate-900 text-sm">Preparing IC memos and partner summaries</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center">
          <p class="text-2xl sm:text-3xl font-bold text-slate-900">
            This is slow. Error-prone. Expensive.
          </p>
        </div>
      </div>
    </section>

    <!-- Features / Solution Section -->
    <section class="bg-gradient-to-b from-green-50 to-white">
      <div class="max-w-7xl mx-auto px-6 py-16">
        <!-- Chip Label -->
        <div class="flex justify-center mb-6">
          <span class="inline-flex items-center px-4 py-1.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full border border-green-200">
            The Solution
          </span>
        </div>

        <h2 class="text-3xl font-bold text-center mb-4">Meet the AI Analyst Built for Professional Investors</h2>
        
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
          <!-- How it works steps -->
          <div class="space-y-6">
            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">1</div>
              </div>
              <div>
                <h3 class="text-lg font-semibold">Upload your data</h3>
                <p class="mt-2 text-sm text-slate-600">CSV files, PDFs, or website URLs — we extract tables and text automatically.</p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">2</div>
              </div>
              <div>
                <h3 class="text-lg font-semibold">Describe in plain English</h3>
                <p class="mt-2 text-sm text-slate-600">No SQL, no formulas — just tell us what insights or charts you need.</p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full font-bold text-lg">3</div>
              </div>
              <div>
                <h3 class="text-lg font-semibold">Get your dashboard</h3>
                <p class="mt-2 text-sm text-slate-600">Interactive HTML dashboards ready in minutes. Edit, share, or export as needed.</p>
              </div>
            </div>
          </div>

          <!-- What you get card -->
          <div class="bg-white border-2 border-green-200 rounded-xl p-6 shadow-lg">
            <h3 class="text-xl font-bold text-slate-900 mb-4">What you get instantly:</h3>
            <ul class="space-y-3">
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">Extracted, cleaned, normalised financials</span>
              </li>
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">YoY, MoM, QoQ performance deltas</span>
              </li>
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">Peer comparisons (if included)</span>
              </li>
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">Automatically built dashboards from PDFs, CSVs or a combination of both</span>
              </li>
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">KPI auto-detection</span>
              </li>
              <li class="flex items-start gap-3">
                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <span class="text-sm text-slate-700">ESG and impact scoring (for relevant reports)</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- See it in Action Section -->
    <section class="bg-gradient-to-b from-slate-50 to-white">
      <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center">
          <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">See it in action</h2>
          <p class="mt-4 text-lg text-slate-600 max-w-3xl mx-auto">
            This dashboard was generated from a <span class="font-semibold text-slate-900">~200 page PDF Tesla report, in 3 minutes</span>.
          </p>
          
          <div class="mt-8">
            <a 
              href="https://viznovo.com/reports/518b8a2e-a752-464e-85a0-d11e3e719de3" 
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white rounded-lg shadow-lg hover:bg-indigo-700 transition text-lg font-medium"
            >
              <span>Explore sample dashboard</span>
              <ExternalLink class="w-5 h-5" />
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="bg-gradient-to-b from-indigo-50 to-white">
      <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
          <h2 class="text-3xl font-bold">One plan. Everything included.</h2>
          <!-- <p class="mt-3 text-slate-600">One plan. Everything included.</p> -->
        </div>

        <div class="max-w-lg mx-auto bg-white border border-slate-200 rounded-xl shadow-lg p-8">
          <div class="text-sm font-semibold text-indigo-600 uppercase tracking-wide">Pro Plan</div>
          <!-- <div class="mt-4 flex items-baseline gap-2">
            <span class="text-5xl font-bold">$24,000</span>
            <span class="text-lg text-slate-600">/year</span>
          </div> -->
          <!-- <div class="text-sm text-slate-500 mt-1">Billed annually ($24,000/year)</div> -->

          <ul class="mt-8 space-y-3 text-sm text-slate-700">
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Unlimited AI dashboards using plain English</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Unlimited PDF and CSV uploads</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Unlimited teammates</span>
            </li>
            <li class="flex items-start gap-2">
              <svg class="w-5 h-5 text-indigo-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span>Granular roles and permissions</span>
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
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-white">
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