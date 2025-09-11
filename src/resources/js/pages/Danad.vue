<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!DOCTYPE html><html lang="en"><head><meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/><title>Girls code program Impact Report</title></head><body class="bg-slate-50 text-gray-600 font-sans">
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
    <!-- 1. Executive Summary -->
    <section class="space-y-6">
      <header class="space-y-2">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-800 tracking-tight">Girls code program Impact Report</h1>
        <p class="text-lg text-gray-600">From anxiety to agency: participants moved from low confidence at Pre to real projects, interviews, and a first tech job by Post.</p>
      </header>

      <!-- Checklist (conceptual steps) -->
      <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center gap-2 mb-3">
          <svg aria-label="checklist icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M8 10h8M8 14h5"/></svg>
          <h2 class="text-slate-800 font-bold text-lg">What this report does</h2>
        </div>
        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
          <li>Segment responses by stage (Pre, Mid, Post) and aggregate key metrics.</li>
          <li>Summarize test scores, project completion, confidence, resume impact, and NPS.</li>
          <li>Extract representative testimonials per stage and surface challenges.</li>
          <li>Flag data gaps or inconsistencies with clear warnings and n-sizes.</li>
          <li>Offer actionable improvements informed by the data.</li>
        </ul>
      </div>

      <!-- Major Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-md flex flex-col items-center p-5">
          <svg aria-label="bar-chart icon" class="w-6 h-6 text-teal-600 mb-2" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="10" width="3" height="8" rx="1"/><rect x="10" y="6" width="3" height="12" rx="1"/><rect x="16" y="4" width="3" height="14" rx="1"/></svg>
          <div class="text-4xl font-extrabold text-[#0E7490]">+7.8</div>
          <div class="text-gray-500 text-sm text-center">Avg test score change (Pre→Mid, n=6). Pre 56.7 → Mid 64.5</div>
        </div>
        <div class="bg-white rounded-lg shadow-md flex flex-col items-center p-5">
          <svg aria-label="check badge icon" class="w-6 h-6 text-teal-600 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>
          <div class="text-4xl font-extrabold text-[#0E7490]">67%</div>
          <div class="text-gray-500 text-sm text-center">Built a web app (Mid, 4/6) — up from 0% (Pre, 0/6)</div>
        </div>
        <div class="bg-white rounded-lg shadow-md flex flex-col items-center p-5">
          <svg aria-label="star icon" class="w-6 h-6 text-teal-600 mb-2" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 18l-5.8 3.1 1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg>
          <div class="text-4xl font-extrabold text-[#0E7490]">+33</div>
          <div class="text-gray-500 text-sm text-center">Net Promoter Score (Mid, n=6). 50% promoters, 17% detractors</div>
        </div>
      </div>
    </section>

    <!-- 2. Key Program Insights -->
    <section class="space-y-4">
      <h2 class="text-2xl font-extrabold text-slate-800">Key Program Insights</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2">
            <svg aria-label="bar-chart icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="10" width="3" height="8" rx="1"/><rect x="10" y="6" width="3" height="12" rx="1"/><rect x="16" y="4" width="3" height="14" rx="1"/></svg>
            <div class="font-bold text-slate-800">Rapid Skills Growth</div>
          </div>
          <p class="text-sm">Average coding test rose by +7.8 points (Pre→Mid, n=6). Notable gains: Ying +32, Triana +31.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2">
            <svg aria-label="check-circle icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>
            <div class="font-bold text-slate-800">Project Outcomes</div>
          </div>
          <p class="text-sm">Built a web app: Pre 0% (0/6) → Mid 67% (4/6) → Post 100% (1/1). Strong shift to applied learning.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2">
            <svg aria-label="growth icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14l5-5 4 4 7-7"/><path d="M20 7v5h-5"/></svg>
            <div class="font-bold text-slate-800">Confidence Shift</div>
          </div>
          <p class="text-sm">At least somewhat confident: Pre 0% (0/5) → Mid 83% (5/6). Confident/very: Mid 33% (2/6).</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2">
            <svg aria-label="briefcase icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="12" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
            <div class="font-bold text-slate-800">Career Readiness</div>
          </div>
          <p class="text-sm">Resume workshop attendance: Mid 100% (6/6). Interview mentions: 50% (3/6). Post: Rosura secured a job and promotions.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2">
            <svg aria-label="info warning icon" class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
            <div class="font-bold text-slate-800">Data Caveats</div>
          </div>
          <p class="text-sm">Post-stage sample is limited (n=1). Pre resume attendance missing. Some fields are “-”. All stats reflect valid-response n.</p>
        </div>
      </div>
    </section>

    <!-- 3. Participant Experience -->
    <section class="space-y-4">
      <h2 class="text-2xl font-extrabold text-slate-800">Participant Experience</h2>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Positives -->
        <div class="bg-white rounded-xl shadow-sm p-5">
          <div class="flex items-center gap-2 mb-3">
            <svg aria-label="checklist icon" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>
            <div class="font-bold text-slate-800">Positives</div>
          </div>
          <div class="space-y-3">
            <div class="border-l-4 border-teal-500 bg-teal-50/40 rounded-md p-3">
              <p class="italic">“The workshop gave me a real edge. Landed several interviews because of my new resume.” <span class="not-italic text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">Mid</span> — Rosura</p>
            </div>
            <div class="border-l-4 border-teal-500 bg-teal-50/40 rounded-md p-3">
              <p class="italic">“I'm now taking advanced computer science classes at school and feel prepared for them.” <span class="not-italic text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">Post</span> — Rosura</p>
            </div>
            <div class="border-l-4 border-teal-500 bg-teal-50/40 rounded-md p-3">
              <p class="italic">“The workshop was a game-changer for my job search. Super grateful.” <span class="not-italic text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">Mid</span> — Ying</p>
            </div>
            <div class="border-l-4 border-teal-500 bg-teal-50/40 rounded-md p-3">
              <p class="italic">“It's been a place where I feel motivated and confident in myself... built friendships.” <span class="not-italic text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">Mid</span> — Triana</p>
            </div>
          </div>
        </div>
        <!-- Challenges -->
        <div class="bg-white rounded-xl shadow-sm p-5">
          <div class="flex items-center gap-2 mb-3">
            <svg aria-label="warning icon" class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
            <div class="font-bold text-slate-800">Challenges</div>
          </div>
          <ul class="space-y-3">
            <li class="flex items-start gap-2">
              <svg aria-label="exclamation icon" class="mt-1 w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
              <span>Pre: Low starting confidence — “I've never coded before and it seems really complicated.” (Rosura, Pre); “coding looks full of difficult math.” (Irina, Pre)</span>
            </li>
            <li class="flex items-start gap-2">
              <svg aria-label="exclamation icon" class="mt-1 w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
              <span>Mid: Confidence is uneven — Ying: “I'm not very confident… need help debugging.” (Mid)</span>
            </li>
            <li class="flex items-start gap-2">
              <svg aria-label="exclamation icon" class="mt-1 w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
              <span>Mid: Attendance/consistency barriers — Pravalika felt overwhelmed and missed many sessions (Mid).</span>
            </li>
            <li class="flex items-start gap-2">
              <svg aria-label="exclamation icon" class="mt-1 w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="currentColor"><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
              <span>Data: Pre resume workshop and most Post metrics are sparse/“–”.</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- 4. Improvements in Confidence & Skills -->
    <section class="space-y-5">
      <h2 class="text-2xl font-extrabold text-slate-800">Improvements in Confidence & Skills</h2>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-slate-800">Test Score (Avg)</span>
            <span class="text-xs text-gray-500">Pre n=6 • Mid n=6</span>
          </div>
          <div class="space-y-1">
            <div class="flex items-center justify-between text-sm"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Pre</span><span class="font-bold text-[#0E7490]">56.7</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-500 rounded" style="width:56.7%"></div></div>
            <div class="flex items-center justify-between text-sm mt-2"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Mid</span><span class="font-bold text-[#0E7490]">64.5</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-600 rounded" style="width:64.5%"></div></div>
          </div>
          <div class="text-xs text-gray-500 mt-2">Change: +7.8 (Pre→Mid)</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-slate-800">Built a Web App</span>
            <span class="text-xs text-gray-500">Valid n — Pre 6 • Mid 6 • Post 1</span>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Pre</span><span class="font-bold text-[#0E7490]">0%</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-500 rounded" style="width:0%"></div></div>
            <div class="flex items-center justify-between text-sm mt-1"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Mid</span><span class="font-bold text-[#0E7490]">67%</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-600 rounded" style="width:67%"></div></div>
            <div class="flex items-center justify-between text-sm mt-1"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Post</span><span class="font-bold text-[#0E7490]">100%</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-700 rounded" style="width:100%"></div></div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-slate-800">Confidence (≥ Somewhat)</span>
            <span class="text-xs text-gray-500">Pre n=5 • Mid n=6</span>
          </div>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Pre</span><span class="font-bold text-[#0E7490]">0%</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-500 rounded" style="width:0%"></div></div>
            <div class="flex items-center justify-between text-sm mt-1"><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Mid</span><span class="font-bold text-[#0E7490]">83%</span></div>
            <div class="w-full h-2 rounded bg-slate-100"><div class="h-2 bg-teal-600 rounded" style="width:83%"></div></div>
          </div>
          <div class="text-xs text-gray-500 mt-2">Confident/Very at Mid: 33% (2/6)</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold text-slate-800">Resume Workshop Impact</span>
            <span class="text-xs text-gray-500">Mid n=6 • Post n=1</span>
          </div>
          <ul class="text-sm space-y-1">
            <li>Attendance: Mid 100% (6/6); Post 100% (1/1)</li>
            <li>Interview mentions: Mid 50% (3/6)</li>
            <li>Positive/neutral feedback: Mid 83% (5/6)</li>
            <li>Post: First tech job & promotions (Rosura)</li>
          </ul>
        </div>
      </div>

      <!-- Data Warning -->
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
        <svg aria-label="warning icon" class="w-5 h-5 text-amber-600 mt-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10.3 3.5L1.8 18a2 2 0 0 0 1.7 3h16.9a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0z"/></svg>
        <p class="text-sm">Post-stage data is limited (n=1), and several Pre fields (e.g., resume attendance) are missing (“–”). Interpret Post and any Pre resume comparisons with caution.</p>
      </div>
    </section>

    <!-- 5. Opportunities to Improve -->
    <section class="space-y-4">
      <h2 class="text-2xl font-extrabold text-slate-800">Opportunities to Improve</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="arrow-up" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg><span class="font-bold text-slate-800">Confidence Bootcamp (Pre)</span></div>
          <p class="text-sm">Short, hands-on intro modules to demystify coding and math—move more learners from “anxious” to “curious.”</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="growth" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14l5-5 4 4 7-7"/><path d="M20 7v5h-5"/></svg><span class="font-bold text-slate-800">Debugging Clinics</span></div>
          <p class="text-sm">Weekly live debugging/code review hours to lift those “not very confident” at Mid.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="star" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 18l-5.8 3.1 1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg><span class="font-bold text-slate-800">Scaffolded App Tracks</span></div>
          <p class="text-sm">Tiered web app paths (starter → intermediate) so every learner ships a project.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="calendar" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4"/></svg><span class="font-bold text-slate-800">Flexible Scheduling</span></div>
          <p class="text-sm">Make-up sessions and on-demand modules to reduce missed content (address Mid overwhelm).</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="bell" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 1 1 12 0v5l2 3H4l2-3V8"/><path d="M10 19a2 2 0 0 0 4 0"/></svg><span class="font-bold text-slate-800">Attendance Nudges</span></div>
          <p class="text-sm">Lightweight reminders, peer study pods, and caregiver updates to sustain consistency.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="spark" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v6M12 16v6M2 12h6M16 12h6M5 5l4 4M15 15l4 4M5 19l4-4M15 9l4-4"/></svg><span class="font-bold text-slate-800">Advanced Pathways</span></div>
          <p class="text-sm">Offer advanced CS and mentorship for high performers (e.g., Ying, Triana) to sustain momentum.</p>
        </div>
      </div>
    </section>

    <!-- 6. FAQ / Need to Know -->
    <section class="space-y-4">
      <h2 class="text-2xl font-extrabold text-slate-800">Need to Know</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="info" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h2v4h-2z"/></svg><span class="font-bold text-slate-800">How were metrics calculated?</span></div>
          <p class="text-sm">We grouped responses by stage (Pre, Mid, Post) and computed averages/percentages using only valid answers (excluding “–”). Each stat shows n.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="star" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 18l-5.8 3.1 1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg><span class="font-bold text-slate-800">What is NPS here?</span></div>
          <p class="text-sm">Net Promoter Score = %Promoters (9–10) − %Detractors (0–6). Mid NPS = +33 (n=6): 50% promoters, 17% detractors, 33% passives.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="bar" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="10" width="3" height="8" rx="1"/><rect x="10" y="6" width="3" height="12" rx="1"/><rect x="16" y="4" width="3" height="14" rx="1"/></svg><span class="font-bold text-slate-800">Why show n-sizes?</span></div>
          <p class="text-sm">Pre resume attendance and most Post metrics are missing (“–”); Post has n=1. We surface n to ensure transparent interpretation.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-2">
          <div class="flex items-center gap-2"><svg aria-label="shield" class="w-5 h-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4v6c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7l8-4z"/></svg><span class="font-bold text-slate-800">How are quotes selected?</span></div>
          <p class="text-sm">Direct participant statements were chosen to represent common sentiments at each stage (positive and challenging).</p>
        </div>
      </div>
    </section>

    <!-- 7. Overall Summary -->
    <section class="space-y-4">
      <h2 class="text-2xl font-extrabold text-slate-800">Overall Summary</h2>
      <div class="bg-white rounded-xl shadow-sm p-6">
        <p class="text-gray-700">The program demonstrates clear impact from Pre to Mid: test scores rise (+7.8), two-thirds ship a web app, and confidence shifts from 0% to 83% “at least somewhat confident.” Career readiness is strong—100% Mid attendance for the resume workshop, frequent interview mentions, and by Post, one alum reports a first tech job and promotions. Mid NPS sits at +33, signaling strong advocacy with room to grow. Key risks remain: uneven confidence (debugging support), attendance gaps, and sparse Post data. Overall, the program effectively converts apprehension into achievement and opportunity for girls exploring tech.</p>
      </div>
    </section>

    <!-- 8. Footer -->
    <footer class="py-8">
      <div class="text-center text-sm text-gray-500">Powered by Sopact, Inc</div>
    </footer>
  </main>
</body></html>
        </div>
    </AppLayout>
</template>
