<template>
  <footer class="footer-deep-blue text-gray-100 mt-8">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
      <!-- Cards row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6 xl:gap-8 items-stretch">
        <!-- Card 1: Brand / Contact -->
        <div class="card h-full">
          <div class="card-body">
            <address class="not-italic text-sm leading-relaxed text-gray-200">
              <h3 class="text-lg font-semibold mb-2 text-white">Address</h3>
              ST. MARK’S COLLEGE NAMAGOMA<br />
              P.O. Box 22888,<br />
              Kampala - Uganda
            </address>

            <div class="mt-3 text-sm space-y-2 text-gray-200">
                 <p class="font-semibold">Telephone - Headteacher’s Office</p>
                  <p><a class="footer-link contact-number" href="tel:+256393103418">+256 393 103 418</a></p>
                  <p><a class="footer-link contact-number" href="tel:+256775831844">+256 775 831 844</a></p>
                  <p><a class="footer-link contact-number" href="tel:+256750330158">+256 750 330 158</a></p>

                  <p class="mt-2 font-semibold">Public Relations Officer (P.R.O)</p>
                  <p><a class="footer-link contact-number" href="tel:+256772996680">+256 772 996 680</a></p>

                  <p class="mt-2 font-semibold">Director’s Office</p>
                  <p><a class="footer-link contact-number" href="mailto:director@stmark.sc.ug">director@stmark.sc.ug</a></p>
            </div>
          </div>
        </div>

        <!-- Card 2: Quick Links & Connect -->
        <div class="card h-full">
          <header class="card-header">
            <h3 class="text-lg font-semibold">Quick Links</h3>
          </header>

          <div class="card-body">
            <ul class="space-y-2 text-sm text-gray-200">
              <li><Link :href="route('home')" class="footer-link">Home</Link></li>
             <li><span class="footer-link opacity-70 cursor-default">About</span></li>
              <span class="footer-link opacity-70 cursor-default">Academics</span>
              <li><Link :href="route('apply')" class="footer-link">Admissions</Link></li>
              <li><Link :href="route('contact')" class="footer-link">Contact</Link></li>
              <li class="footer-link">Cyber Monitor</li>
            </ul>

            <div class="mt-6">
              <h4 class="text-sm font-semibold text-gray-100 mb-3">Connect with us</h4>

              <div class="flex items-center gap-3 flex-wrap">
                <a
                  class="social-btn hover:bg-black"
                  aria-label="X"
                  href="https://x.com/Smacon24"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M18.244 2H21.5l-7.19 8.214L22.5 22h-6.406l-5.012-6.56L5.5 22H2.244l7.692-8.785L1.5 2h6.594l4.512 5.99L18.244 2Z" />
                  </svg>
                </a>

                <a
                  class="social-btn hover:bg-blue-600"
                  aria-label="Facebook"
                  href="https://www.facebook.com/people/St-Marks-College-Namagoma/100057643021727/"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M22 12a10 10 0 10-11.63 9.87v-6.99H7.9V12h2.47V9.8c0-2.44 1.45-3.79 3.67-3.79 1.06 0 2.17.19 2.17.19v2.39h-1.22c-1.2 0-1.57.74-1.57 1.5V12h2.67l-.43 2.88h-2.24v6.99A10 10 0 0022 12z" />
                  </svg>
                </a>

                <a
                  class="social-btn hover:bg-black"
                  aria-label="TikTok"
                  href="https://www.tiktok.com/@smacon23"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12.75 2h3.5a5.25 5.25 0 005.25 5.25v3.5a8.75 8.75 0 01-5.25-1.7v6.45a6.25 6.25 0 11-6.25-6.25c.42 0 .83.04 1.25.12v3.6a2.75 2.75 0 102.75 2.75V2z" />
                  </svg>
                </a>

                <a
                  class="social-btn hover:bg-red-600"
                  aria-label="YouTube"
                  href="https://www.youtube.com/@stmarkscollegenamagoma6864"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M23.5 6.2s-.2-1.7-.9-2.5c-.8-.9-1.7-.9-2.1-1C16.6 2 12 2 12 2s-4.6 0-8.5.7c-.4.1-1.3.1-2.1 1C.6 4.5.5 6.2.5 6.2S0 8.1 0 10v4c0 1.9.5 3.8.5 3.8s.2 1.7.9 2.5c.8.9 1.9.8 2.4.9 1.7.1 7.4.7 7.4.7s4.6 0 8.5-.7c.4-.1 1.3-.1 2.1-1 .7-.8.9-2.5.9-2.5s.5-1.9.5-3.8V10c0-1.9-.5-3.8-.5-3.8z" />
                    <polygon points="9.5,8 16,12 9.5,16" fill="white" />
                  </svg>
                </a>

                <a
                  class="social-btn hover:bg-green-600"
                  aria-label="WhatsApp"
                  href="https://wa.me/256775831844"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C6.48 2 2 6.26 2 11.56c0 1.98.55 3.82 1.52 5.41L2 22l5.17-1.48c1.52.83 3.27 1.27 5.08 1.27 5.52 0 10-4.26 10-9.56S17.52 2 12 2z" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: How to reach us -->
        <div class="card h-full">
          <header class="card-header">
            <h3 class="text-lg font-semibold">How to reach us</h3>
          </header>

          <div class="card-body text-sm text-gray-200">
            <p class="font-semibold">Own transport</p>
            <p class="leading-relaxed mt-1">
              From Kampala drive ≈16 km on Masaka Road (about 20–30 minutes). After Nabbingo Trading Center, look for the College signpost on the right - about 1 km from the main road.
            </p>

            <div class="mt-4">
              <h4 class="font-semibold">Office hours</h4>
              <ul class="mt-2 space-y-1">
                <li>Mon - Fri: 7:30 AM - 5:00 PM</li>
                <li>Sat: 9:00 AM - 1:00 PM</li>
                <li>Sun: Closed</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Card 4: Map -->
        <div class="card h-full">
          <header class="card-header">
            <h3 class="text-lg font-semibold">Find us</h3>
          </header>

          <div class="card-body">
            <div class="map-wrap rounded-md overflow-hidden border border-gray-700 shadow-sm">
              <iframe
                class="map-frame w-full"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="St Mark's College Namagoma - Map"
                :src="embeddedMapUrl"
                aria-label="Map showing St Mark's College Namagoma"
              ></iframe>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <a
                :href="mapUrl"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-yellow-400 text-blue-950 font-semibold hover:bg-yellow-300 transition"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Open in Google Maps"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M12 2C8.1 2 5 5.1 5 9c0 5.3 7 13 7 13s7-7.7 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" />
                </svg>
                View on Google Maps
              </a>

              <a
                :href="directionsUrl"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-gray-600 text-sm text-gray-200 hover:bg-gray-800 transition"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Get directions"
              >
                Get directions
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer bottom -->
      <div class="mt-8 border-t border-gray-700">
        <div class="max-w-8xl mx-auto px-0 py-4 text-center">
          <p class="text-sm md:text-base font-semibold tracking-wide text-white">
            © {{ year }}
            <span class="font-bold text-yellow-400">St Mark's College Namagoma</span>.
            All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const year = new Date().getFullYear()

// 📍 NAMAGOMA STAGE (TURN RIGHT POINT)
const originLat = 0.278844
const originLng = 32.469957

// 📍 SCHOOL (EXACT PIN)
const schoolLat = 0.287574544131338
const schoolLng = 32.47190598057264

// 👉 OPEN MAP (school pin)
const mapUrl = `https://www.google.com/maps?q=${schoolLat},${schoolLng}`

// 👉 EMBED MAP (zoomed properly)
const embeddedMapUrl = `https://www.google.com/maps?q=${schoolLat},${schoolLng}&z=17&output=embed`

// ✅ WAYPOINTS USING REAL PLACES (BEST METHOD)
const waypoints = [
  "Kyengera Uganda",
  "Nabbingo Bus Stage Uganda",
  "Freedom Covenant Church Nabbingo Uganda",
  "Rene Glassmart Namagoma Uganda",
  "Rapha Arena Academy Namagoma Uganda"
].join("|")

// ✅ FINAL DIRECTIONS (FROM KAMPALA → SCHOOL)
const directionsUrl = `https://www.google.com/maps/dir/?api=1&origin=Kampala Uganda&destination=St Mark's College Namagoma Uganda&waypoints=${waypoints}&travelmode=driving`
</script>

<style scoped>
.footer-deep-blue {
  background: linear-gradient(180deg, #042a4a 0%, #021f3a 100%);
  color: #e6eef8;
}

.card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  padding: 18px;
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  min-height: 100%;
  box-shadow: 0 6px 18px rgba(2, 20, 36, 0.45);
}

.card-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
  padding-bottom: 10px;
  margin-bottom: 10px;
}

.card-body {
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.footer-link {
  color: #cfe8ff;
  transition: color 0.15s ease, transform 0.08s ease;
  text-decoration: none;
  word-break: break-word;
}
.footer-link:hover,
.footer-link:focus {
  color: #ffffff;
  transform: translateY(-1px);
  outline: none;
}

.social-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.03);
  color: #cfe8ff;
  transition: background 0.15s ease, transform 0.08s ease;
  flex: 0 0 auto;
}
.social-btn:hover,
.social-btn:focus {
  background: rgba(255, 255, 255, 0.06);
  transform: translateY(-2px);
  color: #ffffff;
  outline: none;
}

.map-wrap {
  width: 100%;
  overflow: hidden;
}

.map-frame {
  display: block;
  border: 0;
  height: 210px;
}

@media (min-width: 1024px) {
  .map-frame {
    height: 240px;
  }

  .card {
    padding: 22px;
  }
}

@media (min-width: 1280px) {
  .map-frame {
    height: 260px;
  }
}

@media (min-width: 1536px) {
  .map-frame {
    height: 280px;
  }
}

.contact-number {
  color: #facc15; /* yellow */
  font-weight: 600;
}

.contact-number:hover {
  color: #fde68a; /* lighter yellow on hover */
}
</style>