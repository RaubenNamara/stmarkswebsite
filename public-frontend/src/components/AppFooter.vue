<script setup lang="ts">
const year = new Date().getFullYear()

const quickLinks: Array<[string, string]> = [
  ['/', 'Home'],
  ['/college-name', 'About'],
  ['/academics', 'Academics'],
  ['/admissions', 'Admissions'],
  ['/contact', 'Contact'],
]

const socialLinks: Array<{ icon: 'x' | 'facebook' | 'tiktok' | 'youtube' | 'whatsapp'; href: string; label: string }> = [
  { icon: 'x', href: 'https://x.com/Smacon24', label: 'X (Twitter)' },
  { icon: 'facebook', href: 'https://www.facebook.com/people/St-Marks-College-Namagoma/100057643021727/', label: 'Facebook' },
  { icon: 'tiktok', href: 'https://www.tiktok.com/@smacon23', label: 'TikTok' },
  { icon: 'youtube', href: 'https://www.youtube.com/@stmarkscollegenamagoma6864', label: 'YouTube' },
  { icon: 'whatsapp', href: 'https://wa.me/256775831844', label: 'WhatsApp' },
]

// Exact pin, not a text search - precise enough that the embedded map and "View on Google Maps"
// both drop the marker right on campus instead of an approximate Masaka Road location.
const schoolLat = 0.287574544131338
const schoolLng = 32.47190598057264
const mapsViewUrl = `https://www.google.com/maps?q=${schoolLat},${schoolLng}`
const mapsEmbedUrl = `https://www.google.com/maps?q=${schoolLat},${schoolLng}&z=17&output=embed`

// Named waypoints along the real Kampala-Masaka Road route (Kyengera, Nabbingo, then the last
// stretch into Namagoma) route more reliably than a straight-line/default driving route would.
const directionsWaypoints = [
  'Kyengera Uganda',
  'Nabbingo Bus Stage Uganda',
  'Freedom Covenant Church Nabbingo Uganda',
  'Rene Glassmart Namagoma Uganda',
  'Rapha Arena Academy Namagoma Uganda',
].join('|')
const mapsDirectionsUrl = `https://www.google.com/maps/dir/?api=1&origin=Kampala+Uganda&destination=St+Mark's+College+Namagoma+Uganda&waypoints=${encodeURIComponent(directionsWaypoints)}&travelmode=driving`
</script>

<template>
  <footer class="bg-brand-navy-dark text-blue-200/80">
    <div class="container-chrome py-14">
      <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <!-- Address -->
        <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10 transition duration-300 hover:-translate-y-1 hover:bg-white/[0.07] hover:shadow-lg hover:ring-white/20">
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Address</h3>
          <p class="mt-3 text-sm font-semibold text-white">St. Mark's College Namagoma</p>
          <p class="text-sm leading-relaxed">P.O. Box 22888, Kampala - Uganda</p>

          <div class="mt-4">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-200/50">Headteacher's Office</p>
            <ul class="mt-1.5 space-y-1 text-sm">
              <li><a href="tel:+256393103418" class="text-blue-200/80 transition hover:text-white">+256 393 103 418</a></li>
              <li><a href="tel:+256775831844" class="text-blue-200/80 transition hover:text-white">+256 775 831 844</a></li>
              <li><a href="tel:+256750330158" class="text-blue-200/80 transition hover:text-white">+256 750 330 158</a></li>
            </ul>
          </div>

          <div class="mt-4">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-200/50">Public Relations Officer (P.R.O)</p>
            <a href="tel:+256772996680" class="mt-1.5 block text-sm text-blue-200/80 transition hover:text-white">+256 772 996 680</a>
          </div>

          <div class="mt-4">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-200/50">Director's Office</p>
            <a href="mailto:director@stmark.sc.ug" class="mt-1.5 block text-sm text-blue-200/80 transition hover:text-white">director@stmark.sc.ug</a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10 transition duration-300 hover:-translate-y-1 hover:bg-white/[0.07] hover:shadow-lg hover:ring-white/20">
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Quick Links</h3>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li v-for="[href, label] in quickLinks" :key="href">
              <router-link :to="href" class="text-blue-200/80 transition hover:text-white">{{ label }}</router-link>
            </li>
            <li>
              <a href="https://stmark.sc.ug/laptop_tracking_system/auth/login.php" target="_blank" rel="noopener" class="text-blue-200/80 transition hover:text-white">Cyber Monitor</a>
            </li>
          </ul>

          <h3 class="mt-8 font-display text-sm font-bold uppercase tracking-wide text-white">Connect with us</h3>
          <div class="mt-4 flex items-center gap-2">
            <a v-for="social in socialLinks" :key="social.icon" :href="social.href" target="_blank" rel="noopener" :aria-label="social.label" :title="social.label" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-blue-100 transition hover:bg-white/20 hover:text-white">
              <svg v-if="social.icon === 'x'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-7.6 8.7L23.3 22H16.7l-5.2-6.8L5.5 22H2.4l8.1-9.3L1.5 2h6.8l4.7 6.2L18.9 2zm-1.2 18h1.7L7.4 4h-1.8l12.1 16z" /></svg>
              <svg v-else-if="social.icon === 'facebook'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z" /></svg>
              <svg v-else-if="social.icon === 'tiktok'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M16.5 2h-3v13.5a2.5 2.5 0 11-2.5-2.5c.17 0 .34.02.5.05V9.9a5.9 5.9 0 105.1 5.85V9.1a7.6 7.6 0 004.4 1.4V7.4A4.5 4.5 0 0116.5 2z" /></svg>
              <svg v-else-if="social.icon === 'youtube'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23 12s0-3.4-.4-5a3 3 0 00-2.1-2.1C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.5.4A3 3 0 001.4 7C1 8.6 1 12 1 12s0 3.4.4 5a3 3 0 002.1 2.1c1.6.4 8.5.4 8.5.4s6.9 0 8.5-.4a3 3 0 002.1-2.1c.4-1.6.4-5 .4-5zM9.8 15.5v-7l6 3.5-6 3.5z" /></svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.6-.8-1.9-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.5.1-.3-.1-1.2-.4-2.2-1.4-.8-.7-1.4-1.6-1.5-1.9-.2-.3 0-.4.1-.6l.4-.5c.1-.1.2-.3.2-.4.1-.2 0-.3 0-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-1 1-1 2.3s1 2.7 1.1 2.9c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.6-.7 1.9-1.3.2-.6.2-1.1.2-1.3-.1-.1-.3-.2-.6-.3z" /><path d="M12 2C6.5 2 2 6.4 2 11.8c0 2 .6 3.9 1.6 5.5L2 22l4.9-1.5c1.6.9 3.3 1.3 5.1 1.3 5.5 0 10-4.4 10-9.8S17.5 2 12 2zm0 17.8c-1.6 0-3.2-.4-4.5-1.2l-.3-.2-3.3 1 1-3.1-.2-.3c-.9-1.4-1.4-3.1-1.4-4.8C3.3 6.9 7.2 3 12 3s8.7 3.9 8.7 8.8-3.9 8-8.7 8z" /></svg>
            </a>
          </div>
        </div>

        <!-- How to reach us -->
        <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10 transition duration-300 hover:-translate-y-1 hover:bg-white/[0.07] hover:shadow-lg hover:ring-white/20">
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">How to reach us</h3>

          <p class="mt-4 text-xs font-bold uppercase tracking-wide text-blue-200/50">Own transport</p>
          <p class="mt-1.5 text-sm leading-relaxed">From Kampala drive &asymp;16 km on Masaka Road (about 20&ndash;30 minutes). After Nabbingo Trading Center, look for the College signpost on the right, about 1 km from the main road.</p>

          <p class="mt-5 text-xs font-bold uppercase tracking-wide text-blue-200/50">Office hours</p>
          <ul class="mt-1.5 space-y-1 text-sm">
            <li class="flex justify-between gap-4"><span>Mon &ndash; Fri</span><span>7:30 AM &ndash; 5:00 PM</span></li>
            <li class="flex justify-between gap-4"><span>Sat</span><span>9:00 AM &ndash; 1:00 PM</span></li>
            <li class="flex justify-between gap-4"><span>Sun</span><span>Closed</span></li>
          </ul>
        </div>

        <!-- Find us -->
        <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10 transition duration-300 hover:-translate-y-1 hover:bg-white/[0.07] hover:shadow-lg hover:ring-white/20">
          <h3 class="font-display text-sm font-bold uppercase tracking-wide text-white">Find us</h3>
          <div class="mt-4 overflow-hidden rounded-xl ring-1 ring-white/10">
            <iframe :src="mapsEmbedUrl" title="Map showing St Mark's College Namagoma" class="h-64 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" />
          </div>
          <ul class="mt-4 space-y-2.5 text-sm">
            <li>
              <a :href="mapsViewUrl" target="_blank" rel="noopener" class="group/item flex items-center gap-1.5 text-blue-200/80 transition hover:text-white">
                View on Google Maps
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 opacity-0 transition group-hover/item:translate-x-0.5 group-hover/item:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
              </a>
            </li>
            <li>
              <a :href="mapsDirectionsUrl" target="_blank" rel="noopener" class="group/item flex items-center gap-1.5 text-blue-200/80 transition hover:text-white">
                Get directions
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 opacity-0 transition group-hover/item:translate-x-0.5 group-hover/item:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-12 border-t border-white/10 pt-6 text-center text-sm">
        <p>&copy; {{ year }} St Mark's College Namagoma. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
