<script setup lang="ts">
const year = new Date().getFullYear()

const quickLinks: Array<[string, string]> = [
  ['/about', 'About Us'],
  ['/academics', 'Academics'],
  ['/admissions', 'Admissions'],
  ['/news', 'News'],
  ['/staff', 'Staff Directory'],
]

const exploreLinks: Array<[string, string]> = [
  ['/clubs', 'Clubs'],
  ['/explore/gallery', 'Gallery'],
  ['/campus-voices', 'Campus Voices'],
  ['/empowerment-programmes', 'Empowerment'],
  ['/fee-structures', 'Fee Structures'],
]
</script>

<template>
  <footer class="bg-brand-navy-dark text-blue-200/80">
    <div class="mx-auto max-w-6xl px-6 py-14">
      <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <div class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-gold font-display text-sm font-extrabold text-brand-navy-dark">SM</span>
            <span class="font-display text-base font-bold text-white">St Mark's College</span>
          </div>
          <p class="mt-4 text-sm leading-relaxed">The Higher Achiever's College — building disciplined, confident and competent learners in Namagoma, Uganda.</p>
        </div>

        <div>
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Quick Links</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="[href, label] in quickLinks" :key="href">
              <router-link :to="href" class="text-blue-200/80 transition hover:text-white">{{ label }}</router-link>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Explore</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="[href, label] in exploreLinks" :key="href">
              <router-link :to="href" class="text-blue-200/80 transition hover:text-white">{{ label }}</router-link>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Get in Touch</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li>
              <a href="mailto:info@stmark.sc.ug" class="text-blue-200/80 transition hover:text-white">info@stmark.sc.ug</a>
            </li>
            <li><router-link to="/contact" class="text-blue-200/80 transition hover:text-white">Contact form &rarr;</router-link></li>
            <li><router-link to="/apply" class="text-blue-200/80 transition hover:text-white">Careers &rarr;</router-link></li>
          </ul>
        </div>
      </div>

      <div class="mt-12 border-t border-white/10 pt-6 text-center text-sm">
        <p>&copy; {{ year }} St Mark's College Namagoma. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
