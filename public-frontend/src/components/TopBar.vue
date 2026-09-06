<script setup lang="ts">
// Social links are visual placeholders only (no real profile URLs available yet) - wire up
// real hrefs here once provided.
const socialIcons = ['x', 'facebook', 'tiktok', 'youtube', 'chat'] as const
</script>

<template>
  <div class="bg-brand-navy-dark text-blue-100">
    <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-center gap-x-5 gap-y-1.5 px-4 py-2 text-[11px] sm:justify-between sm:px-6 sm:text-xs xl:px-10">
      <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5">
        <a href="tel:+256775831844" class="flex items-center gap-1.5 whitespace-nowrap text-blue-100 transition hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-.75 1.643l-1.026.513a11.05 11.05 0 005.516 5.516l.513-1.026a1.5 1.5 0 011.643-.75l3.223.716A1.5 1.5 0 0117 15.352V16.5a1.5 1.5 0 01-1.5 1.5H14C6.82 18 1 12.18 1 5V3.5z" /></svg>
          +256-775-831844
        </a>
        <a href="mailto:info@stmark.sc.ug" class="flex items-center gap-1.5 whitespace-nowrap text-blue-100 transition hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a2 2 0 00-2 2v.01L10 12l9-5.99V6a2 2 0 00-2-2H3z" /><path d="M18 8.24l-7.514 5.01a1 1 0 01-1.11 0L2 8.24V14a2 2 0 002 2h12a2 2 0 002-2V8.24z" /></svg>
          info@stmark.sc.ug
        </a>
      </div>

      <div class="flex items-center gap-2 sm:gap-3">
        <router-link to="/admissions" class="whitespace-nowrap rounded-md border border-brand-gold px-2.5 py-1 font-bold text-brand-gold transition hover:bg-brand-gold hover:text-brand-navy-dark sm:px-3">ADMISSIONS</router-link>
        <router-link to="/elearning" class="whitespace-nowrap rounded-md border border-emerald-400 px-2.5 py-1 font-bold text-emerald-400 transition hover:bg-emerald-400 hover:text-brand-navy-dark sm:px-3">eLearning</router-link>
      </div>

      <div class="hidden items-center gap-3 md:flex">
        <span class="text-blue-200">Follow us</span>
        <span v-for="icon in socialIcons" :key="icon" class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-blue-100 transition hover:bg-white/20 hover:text-white" :title="icon">
          <svg v-if="icon === 'x'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
          <svg v-else-if="icon === 'facebook'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z" /></svg>
          <svg v-else-if="icon === 'tiktok'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16.5 2h-3v13.5a2.5 2.5 0 11-2.5-2.5c.17 0 .34.02.5.05V9.9a5.9 5.9 0 105.1 5.85V9.1a7.6 7.6 0 004.4 1.4V7.4A4.5 4.5 0 0116.5 2z" /></svg>
          <svg v-else-if="icon === 'youtube'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M23 12s0-3.4-.4-5a3 3 0 00-2.1-2.1C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.5.4A3 3 0 001.4 7C1 8.6 1 12 1 12s0 3.4.4 5a3 3 0 002.1 2.1c1.6.4 8.5.4 8.5.4s6.9 0 8.5-.4a3 3 0 002.1-2.1c.4-1.6.4-5 .4-5zM9.8 15.5v-7l6 3.5-6 3.5z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 5.9 2 10.7c0 2.7 1.4 5.1 3.7 6.7-.1 1-.5 2.5-.8 3.4 0 0 1.9-.5 3.6-1.6.8.2 1.6.3 2.5.3 5.5 0 10-3.9 10-8.8S17.5 2 12 2z" /></svg>
        </span>
      </div>
    </div>
  </div>
</template>
