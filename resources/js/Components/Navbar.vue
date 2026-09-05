<template>
  <div>
    <!-- ================= FIXED HEADER (CONTACT BAR + MAIN NAV) ================= -->
    <header
      ref="headerRef"
      class="fixed inset-x-0 top-0 z-50 bg-white/0 pointer-events-auto"
      aria-hidden="false"
    >
      <!-- CONTACT BAR (desktop) -->
      <div class="hidden md:block bg-gradient-to-r from-[#07203a] via-[#0b2f56] to-[#0b1f3a] text-white">
        <div class="max-w-[1800px] 2xl:max-w-[2000px] mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center justify-between py-2 gap-4">
            <!-- LEFT: Phone + Email -->
            <div class="flex flex-wrap items-center gap-4 md:gap-6 lg:gap-8 xl:gap-10 text-sm sm:text-base font-semibold">
              <a
                href="tel:+256775831844"
                class="flex items-center gap-2 hover:text-yellow-400 transition"
                aria-label="Call"
              >
                <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5a2 2 0 012-2h2.28a2 2 0 011.94 1.515l.516 2.064a2 2 0 01-.45 1.872l-1.27 1.27a16.001 16.001 0 006.586 6.586l1.27-1.27a2 2 0 011.872-.45l2.064.516A2 2 0 0119.72 17H22a2 2 0 012 2v1a2 2 0 01-2 2h-1C10.82 22 2 13.18 2 3V2a2 2 0 012-2h1z"
                  />
                </svg>
                <span>+256-775-831844</span>
              </a>

              <a
                href="mailto:info@stmark.sc.ug"
                class="flex items-center gap-2 hover:text-yellow-400 transition"
                aria-label="Email"
              >
                <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-2.586 2.586A2 2 0 0116 16H8a2 2 0 01-1.414-.586L4 13m16 0H4"
                  />
                </svg>
                <span>info@stmark.sc.ug</span>
              </a>
            </div>

      <!-- CENTER: ADMISSIONS + eSpace BUTTONS -->
<div class="flex items-center gap-3">
  <!-- Admissions -->
  <Link
    :href="safeRoute('admissions')"
    class="border-4 border-yellow-400 text-yellow-400 font-bold px-3 py-1 text-xs tracking-wide rounded-sm transition-all duration-300 ease-in-out hover:bg-yellow-400 hover:text-blue-950 hover:shadow-lg hover:-translate-y-0.5"
    aria-label="Admissions"
  >
    ADMISSIONS
  </Link>

  <!-- eSpace -->
  <a
    href="https://stmark.sc.ug/elearning/"
    target="_blank"
    rel="noopener noreferrer"
    class="border-4 border-green-400 text-green-400 font-bold px-3 py-1 text-xs tracking-wide rounded-sm transition-all duration-300 ease-in-out hover:bg-green-400 hover:text-blue-950 hover:shadow-lg hover:-translate-y-0.5"
    aria-label="eSpace"
  >
    eLearning
  </a>
</div>
            <!-- RIGHT: Social Icons -->
            <div class="flex items-center gap-3">
              <span class="hidden md:inline text-sm font-semibold text-blue-200 mr-2">
                Follow us
              </span>

              <!-- X -->
              <a
                href="https://x.com/Smacon24"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2 rounded-full bg-white/10 hover:bg-black transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1"
                aria-label="X"
              >
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-white transition" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M18.244 2H21.5l-7.19 8.214L22.5 22h-6.406l-5.012-6.56L5.5 22H2.244l7.692-8.785L1.5 2h6.594l4.512 5.99L18.244 2Z"/>
                </svg>
                <span class="tooltip">X</span>
              </a>

              <!-- Facebook -->
              <a
                href="https://www.facebook.com/people/St-Marks-College-Namagoma/100057643021727/"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2 rounded-full bg-white/10 hover:bg-blue-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1"
                aria-label="Facebook"
              >
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-white transition" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M22 12a10 10 0 10-11.63 9.87v-6.99H7.9V12h2.47V9.8c0-2.44 1.45-3.79 3.67-3.79 1.06 0 2.17.19 2.17.19v2.39h-1.22c-1.2 0-1.57.74-1.57 1.5V12h2.67l-.43 2.88h-2.24v6.99A10 10 0 0022 12z"/>
                </svg>
                <span class="tooltip">Facebook</span>
              </a>

              <!-- TikTok -->
              <a
                href="https://www.tiktok.com/@smacon23"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2 rounded-full bg-white/10 hover:bg-black transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1"
                aria-label="TikTok"
              >
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-white transition" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.75 2h3.5a5.25 5.25 0 005.25 5.25v3.5a8.75 8.75 0 01-5.25-1.7v6.45a6.25 6.25 0 11-6.25-6.25c.42 0 .83.04 1.25.12v3.6a2.75 2.75 0 102.75 2.75V2z"/>
                </svg>
                <span class="tooltip">TikTok</span>
              </a>

              <!-- YouTube -->
              <a
                href="https://www.youtube.com/@stmarkscollegenamagoma6864"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2 rounded-full bg-white/10 hover:bg-red-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1"
                aria-label="YouTube"
              >
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-white transition" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M23.5 6.2s-.2-1.7-.9-2.5c-.8-.9-1.7-.9-2.1-1C16.6 2 12 2 12 2s-4.6 0-8.5.7c-.4.1-1.3.1-2.1 1C.6 4.5.5 6.2.5 6.2S0 8.1 0 10v4c0 1.9.5 3.8.5 3.8s.2 1.7.9 2.5c.8.9 1.9.8 2.4.9 1.7.1 7.4.7 7.4.7s4.6 0 8.5-.7c.4-.1 1.3-.1 2.1-1 .7-.8.9-2.5.9-2.5s.5-1.9.5-3.8V10c0-1.9-.5-3.8-.5-3.8z"/>
                  <polygon points="9.5,8 16,12 9.5,16" fill="white"/>
                </svg>
                <span class="tooltip">YouTube</span>
              </a>

              <!-- WhatsApp -->
              <a
                href="https://wa.me/256775831844"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative p-2 rounded-full bg-white/10 hover:bg-green-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-1"
                aria-label="WhatsApp"
              >
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-white transition" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.26 2 11.56c0 1.98.55 3.82 1.52 5.41L2 22l5.17-1.48c1.52.83 3.27 1.27 5.08 1.27 5.52 0 10-4.26 10-9.56S17.52 2 12 2z"/>
                </svg>
                <span class="tooltip">WhatsApp</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTACT BAR (mobile compact) -->
      <div class="md:hidden bg-gradient-to-r from-[#07203a] via-[#0b2f56] to-[#0b1f3a] text-white">
        <div class="w-full px-3 py-2 flex flex-col gap-2">
          <!-- TOP ROW -->
          <div class="flex items-center justify-between gap-2">
            <!-- Phone -->
            <a
              href="tel:+256775831844"
              class="flex items-center gap-1.5 text-[11px] font-semibold whitespace-nowrap hover:text-yellow-400"
              aria-label="Call"
            >
              <svg class="w-4 h-4 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 5a2 2 0 012-2h2.28a2 2 0 011.94 1.515l.516 2.064a2 2 0 01-.45 1.872l-1.27 1.27a16.001 16.001 0 006.586 6.586l1.27-1.27a2 2 0 011.872-.45l2.064.516A2 2 0 0119.72 17H22a2 2 0 012 2v1a2 2 0 01-2 2h-1C10.82 22 2 13.18 2 3V2a2 2 0 012-2h1z"
                />
              </svg>
              <span>+256-775-831844</span>
            </a>

            <!-- Admissions + eSpace -->
<div class="flex items-center gap-2">
  <!-- Admissions -->
  <Link
    :href="safeRoute('admissions')"
    class="px-2 py-1 border border-yellow-400 rounded text-yellow-400 text-[11px] font-bold whitespace-nowrap hover:bg-yellow-400 hover:text-blue-950 transition"
  >
    ADMISSIONS
  </Link>

  <!-- eSpace -->
  <a
    href="https://stmark.sc.ug/elearning/"
    target="_blank"
    rel="noopener noreferrer"
    class="px-2 py-1 border border-green-400 rounded text-green-400 text-[11px] font-bold whitespace-nowrap hover:bg-green-400 hover:text-blue-950 transition"
  >
    eLearning
  </a>
</div>

            <!-- Menu -->
            <button
              @click="openMobile"
              class="p-2 rounded-lg hover:bg-blue-800 transition"
              aria-label="Open menu"
              type="button"
            >
              <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- MAIN NAV -->
      <nav class="bg-yellow-100/95 backdrop-blur-sm shadow-md border-b border-yellow-300">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 2xl:px-20">
          <div class="flex justify-between items-center h-16 md:h-20 lg:h-24 xl:h-28">
            <!-- Logo -->
          <Link href="/" class="flex items-center gap-2 flex-shrink-0 max-w-[260px]">

  <!-- Logo -->
  <img
    src="/images/logo.png"
    alt="St Mark's College Namagoma Logo"
    class="h-10 md:h-12 lg:h-14 xl:h-16 w-auto object-contain"
  />

  <!-- Text -->
 <div class="flex flex-col items-start leading-tight max-w-[150px] lg:max-w-[180px]">

    <span class="text-gray-900 font-bold 
                 text-sm md:text-base lg:text-lg xl:text-xl leading-tight">
      St Mark's College
    </span>

    <span class="text-gray-800 font-semibold 
                 text-xs md:text-sm lg:text-base">
      Namagoma
    </span>

    <!-- Motto -->
    <span class="hidden md:block text-blue-900 
                 text-[9px] lg:text-[11px] italic leading-tight">
      "The Higher Achiever's College"
    </span>

  </div>

</Link>

            <!-- Desktop Menu -->
          <div class="hidden md:flex items-center justify-end flex-1 min-w-0 gap-1 lg:gap-2 xl:gap-3 2xl:gap-4">
              <Link :href="safeRoute('home')" class="nav-link">Home</Link>

              <!-- Explore dropdown -->
              <div class="relative group">
                <button class="nav-link flex items-center gap-2" aria-haspopup="true" aria-expanded="false" type="button">
                  <span>Explore</span>
                  <svg
                    class="w-4 h-4 mt-1 transition-transform duration-300 group-hover:rotate-180"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  class="absolute left-0 mt-2 w-56 lg:w-64 xl:w-72 bg-white shadow-xl rounded-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-1 group-hover:translate-y-0 transition-all duration-200 z-50"
                  role="menu"
                  aria-label="Explore submenu"
                >
                  <Link :href="safeRoute('explore.career')" class="dropdown-item" role="menuitem">Career</Link>
                  <Link :href="safeRoute('explore.fees')" class="dropdown-item" role="menuitem">Fees, Rules & Caledar</Link>
                  <Link :href="safeRoute('explore.student-leadership')" class="dropdown-item" role="menuitem">
                    Student Leadership
                  </Link>
                  <Link :href="safeRoute('explore.uniform')" class="dropdown-item" role="menuitem">Uniform</Link>
                  <Link :href="safeRoute('explore.gallery')" class="dropdown-item" role="menuitem">Gallery</Link>
                  <Link :href="safeRoute('explore.campus-voices')" class="dropdown-item" role="menuitem">Campus Voices</Link>
                </div>
              </div>

              <!-- About dropdown -->
              <div class="relative group">
                <button class="nav-link flex items-center gap-2" aria-haspopup="true" aria-expanded="false" type="button">
                  <span>About</span>
                  <svg
                    class="w-4 h-4 mt-1 transition-transform duration-300 group-hover:rotate-180"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  class="absolute left-0 mt-2 w-56 bg-white shadow-xl rounded-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-1 group-hover:translate-y-0 transition-all duration-200 z-50"
                  role="menu"
                  aria-label="About submenu"
                >
                  <Link :href="safeRoute('staff')" class="dropdown-item" role="menuitem">Staff</Link>
                  <Link :href="safeRoute('board')" class="dropdown-item" role="menuitem">Board Members</Link>
                  <Link :href="safeRoute('anthem')" class="dropdown-item" role="menuitem">School Anthem</Link>
                  <Link :href="safeRoute('college-name')" class="dropdown-item" role="menuitem">College Name</Link>
                  <Link :href="safeRoute('clubs.index')" class="dropdown-item">Clubs</Link>
                </div>
              </div>

              <!-- Digital Campus -->
              <div class="relative group">
                <button type="button" class="nav-link flex items-center gap-2" aria-haspopup="true" aria-expanded="false">
                  eCampus
                  <svg
                    class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                  role="menu"
                  aria-label="Digital Campus submenu"
                >
                  <a href="https://stmark.sc.ug/elearning/" target="_blank" rel="noopener noreferrer" class="dropdown-item" role="menuitem">eLearning</a>
                  <a href="http://smacondecides.center4webpresence.com/" target="_blank" rel="noopener noreferrer" class="dropdown-item" role="menuitem">eVoting System</a>
                  <a href="https://stmark.sc.ug/laptop_tracking_system/auth/login.php" target="_blank" rel="noopener noreferrer" class="dropdown-item" role="menuitem">Cyber Monitor</a>
                  <a href="https://stmark.sc.ug/concerting/" target="_blank" rel="noopener noreferrer" class="dropdown-item" role="menuitem">eTicketing</a>
                </div>
              </div>

              <!-- Academics -->
              <div class="relative group">
                <button type="button" class="nav-link flex items-center gap-2" aria-haspopup="true" aria-expanded="false">
                  Academics
                  <svg
                    class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  class="absolute left-0 mt-2 w-64 bg-white shadow-xl rounded-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-1 group-hover:translate-y-0 transition-all duration-200 z-50"
                  role="menu"
                  aria-label="Academics submenu"
                >
                  <Link :href="safeRoute('academics.curriculum')" class="dropdown-item" role="menuitem">Curriculum</Link>
                  <Link :href="safeRoute('academics.co-curricular')" class="dropdown-item" role="menuitem">Co-Curricular</Link>
                  <Link :href="safeRoute('performance.index')" class="dropdown-item">Performance & Circulars</Link>
                  <Link :href="safeRoute('academics.high-achievers')" class="dropdown-item" role="menuitem">High Achiever's Page</Link>
                </div>
              </div>

              <!-- Empowerment -->
              <div class="relative group">
                <button type="button" class="nav-link flex items-center gap-2" aria-haspopup="true" aria-expanded="false">
                  Empowerment
                  <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div
                  class="absolute left-0 mt-2 w-72 bg-white shadow-xl rounded-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transform translate-y-1 group-hover:translate-y-0 transition-all duration-200 z-50"
                  role="menu"
                  aria-label="Empowerment submenu"
                >
                  <Link :href="safeRoute('empowerment.chaplaincy')" class="dropdown-item">Chaplaincy</Link>
                  <Link :href="safeRoute('empowerment.mentorship')" class="dropdown-item">Mentorship Programme</Link>
                  <Link :href="safeRoute('empowerment.girl-boy-talk')" class="dropdown-item">Girl-Boy Talk</Link>
                  <Link :href="safeRoute('empowerment.inspiration-night')" class="dropdown-item">Inspiration Night</Link>
                  <Link :href="safeRoute('empowerment.smosa-alumni')" class="dropdown-item">SMOSA Alumni</Link>
                  <Link :href="safeRoute('empowerment.smosa-feedback')" class="dropdown-item">SMOSA Feedback</Link>
                  <Link :href="safeRoute('empowerment.christmas-cantata')" class="dropdown-item">Christmas Cantata</Link>
                </div>
              </div>

              <Link :href="safeRoute('contact')" class="nav-link">Contact</Link>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- ================= MAIN (pushed down by fixed header) ================= -->
    <main :style="{ paddingTop: headerHeight + 'px' }" class="pb-0">
      <!-- LATEST POSTS -->
      <transition name="fade">
        <div
          v-show="showTicker"
          class="bg-gradient-to-r from-blue-950 via-blue-900 to-blue-950 text-white border-t border-blue-800"
        >
          <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-center py-2 gap-2">
              <div class="flex items-center gap-2 font-semibold text-blue-200 shrink-0">
                <span class="relative flex h-3 w-3 md:h-4 md:w-4">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 md:h-4 md:w-4 bg-yellow-400 shadow-lg"></span>
                </span>
                <span class="text-sm md:text-lg whitespace-nowrap">
                  Latest Posts
                </span>
              </div>

              <div
                class="flex-1 min-w-0 overflow-hidden h-7 md:h-8 relative flex items-center"
                @mouseenter="pauseRotation"
                @mouseleave="resumeRotation"
              >
                <transition name="ticker" mode="out-in">
                  <Link
                    v-if="currentPost"
                    :key="currentPostKey"
                    :href="safeRoute('posts.show', currentPost.slug)"
                    class="latest-moving-link block text-xs md:text-base truncate"
                    aria-live="polite"
                  >
                    {{ currentPost.title }}
                  </Link>

                  <div v-else class="latest-moving-link text-xs md:text-base">
                    No posts yet
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <slot />
    </main>

    <!-- ================= MOBILE STICKY BOTTOM SOCIAL BAR ================= -->
    <transition name="fade">
      <div
        v-show="!mobileOpen"
        class="md:hidden fixed inset-x-0 bottom-0 z-[60] bg-gradient-to-r from-[#071b35] via-[#0b2f56] to-[#07162b] border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.25)]"
        :style="{ paddingBottom: 'env(safe-area-inset-bottom)' }"
      >
        <div class="grid grid-cols-5">
          <a
            href="https://x.com/Smacon24"
            target="_blank"
            rel="noopener noreferrer"
            class="flex flex-col items-center justify-center py-2 text-white/90 hover:bg-white/10 transition"
            aria-label="X"
          >
            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2H21.5l-7.19 8.214L22.5 22h-6.406l-5.012-6.56L5.5 22H2.244l7.692-8.785L1.5 2h6.594l4.512 5.99L18.244 2Z"/>
            </svg>
          
          </a>

          <a
            href="https://www.facebook.com/people/St-Marks-College-Namagoma/100057643021727/"
            target="_blank"
            rel="noopener noreferrer"
            class="flex flex-col items-center justify-center py-2 text-white/90 hover:bg-white/10 transition"
            aria-label="Facebook"
          >
            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22 12a10 10 0 10-11.63 9.87v-6.99H7.9V12h2.47V9.8c0-2.44 1.45-3.79 3.67-3.79 1.06 0 2.17.19 2.17.19v2.39h-1.22c-1.2 0-1.57.74-1.57 1.5V12h2.67l-.43 2.88h-2.24v6.99A10 10 0 0022 12z"/>
            </svg>
            
          </a>

          <a
            href="https://www.tiktok.com/@smacon23"
            target="_blank"
            rel="noopener noreferrer"
            class="flex flex-col items-center justify-center py-2 text-white/90 hover:bg-white/10 transition"
            aria-label="TikTok"
          >
            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.75 2h3.5a5.25 5.25 0 005.25 5.25v3.5a8.75 8.75 0 01-5.25-1.7v6.45a6.25 6.25 0 11-6.25-6.25c.42 0 .83.04 1.25.12v3.6a2.75 2.75 0 102.75 2.75V2z"/>
            </svg>
            <span class="mt-1 text-[10px] font-semibold leading-none">TikTok</span>
          </a>

          <a
            href="https://www.youtube.com/@stmarkscollegenamagoma6864"
            target="_blank"
            rel="noopener noreferrer"
            class="flex flex-col items-center justify-center py-2 text-white/90 hover:bg-white/10 transition"
            aria-label="YouTube"
          >
            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M23.5 6.2s-.2-1.7-.9-2.5c-.8-.9-1.7-.9-2.1-1C16.6 2 12 2 12 2s-4.6 0-8.5.7c-.4.1-1.3.1-2.1 1C.6 4.5.5 6.2.5 6.2S0 8.1 0 10v4c0 1.9.5 3.8.5 3.8s.2 1.7.9 2.5c.8.9 1.9.8 2.4.9 1.7.1 7.4.7 7.4.7s4.6 0 8.5-.7c.4-.1 1.3-.1 2.1-1 .7-.8.9-2.5.9-2.5s.5-1.9.5-3.8V10c0-1.9-.5-3.8-.5-3.8z"/>
              <polygon points="9.5,8 16,12 9.5,16" fill="white"/>
            </svg>
          
          </a>

          <a
            href="https://wa.me/256775831844"
            target="_blank"
            rel="noopener noreferrer"
            class="flex flex-col items-center justify-center py-2 text-white/90 hover:bg-white/10 transition"
            aria-label="WhatsApp"
          >
            <svg class="w-5 h-5 text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.26 2 11.56c0 1.98.55 3.82 1.52 5.41L2 22l5.17-1.48c1.52.83 3.27 1.27 5.08 1.27 5.52 0 10-4.26 10-9.56S17.52 2 12 2z"/>
            </svg>
            
          </a>
        </div>
      </div>
    </transition>

    <!-- ================= MOBILE OVERLAY + PROFESSIONAL SIDEBAR ================= -->
    <transition name="fade">
      <div
        v-if="mobileOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-[65]"
        @click="closeMobile"
      ></div>
    </transition>

    <transition name="drawer">
      <aside
        v-if="mobileOpen"
        id="mobile-menu"
        class="fixed left-0 z-[70] w-80 max-w-[86vw] bg-gradient-to-b from-[#0b2a53] to-[#07162b] shadow-2xl ring-1 ring-white/10 overflow-y-auto"
        :style="{ top: headerHeight + 'px', height: `calc(100vh - ${headerHeight}px)` }"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile menu"
      >
        <div class="flex justify-end px-4 py-4 border-b border-white/10 bg-white/5">
          <button
            @click="closeMobile"
            class="grid place-items-center h-10 w-10 rounded-full text-white hover:bg-white/10 transition"
            aria-label="Close menu"
            type="button"
          >
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <nav class="flex flex-col gap-3 px-3 pb-6">
          <Link @click="closeMobile" :href="safeRoute('home')" class="mobile-item">
            <span>Home</span>
          </Link>

          <div class="mobile-section">
            <button @click="toggleExploreMobile" class="w-full flex items-center justify-between mobile-item" type="button">
              <span>Explore</span>
              <svg :class="{ 'rotate-180': exploreMobileOpen }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.44l3.71-4.21a.75.75 0 111.12 1L10.56 13a.75.75 0 01-1.12 0L5.21 8.24a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <div v-show="exploreMobileOpen" class="px-2 pb-2">
              <Link @click="closeMobile" :href="safeRoute('explore.career')" class="mobile-sub-item">Career</Link>
              <Link @click="closeMobile" :href="safeRoute('explore.fees')" class="mobile-sub-item">Fees, Rules & Caledar</Link>
                            <Link @click="closeMobile" :href="safeRoute('explore.student-leadership')" class="mobile-sub-item">
                Student Leadership
              </Link>
              <Link @click="closeMobile" :href="safeRoute('explore.uniform')" class="mobile-sub-item">Uniform</Link>
              <Link @click="closeMobile" :href="safeRoute('explore.gallery')" class="mobile-sub-item">Gallery</Link>
              <Link @click="closeMobile" :href="safeRoute('explore.campus-voices')" class="mobile-sub-item">Campus Voices</Link>
            </div>
          </div>

          <div class="mobile-section">
            <button @click="toggleAboutMobile" class="w-full flex items-center justify-between mobile-item" type="button">
              <span>About</span>
              <svg :class="{ 'rotate-180': aboutOpen }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.44l3.71-4.21a.75.75 0 111.12 1L10.56 13a.75.75 0 01-1.12 0L5.21 8.24a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <div v-show="aboutOpen" class="px-2 pb-2">
              <Link @click="closeMobile" :href="safeRoute('staff')" class="mobile-sub-item">Staff</Link>
              <Link @click="closeMobile" :href="safeRoute('board')" class="mobile-sub-item">Board Members</Link>
              <Link @click="closeMobile" :href="safeRoute('anthem')" class="mobile-sub-item">School Anthem</Link>
              <Link @click="closeMobile" :href="safeRoute('college-name')" class="mobile-sub-item">College Name</Link>
              <Link @click="closeMobile" :href="safeRoute('clubs.index')" class="mobile-sub-item">Clubs</Link>
            </div>
          </div>

          <div class="mobile-section">
            <button @click="toggleDigitalMobile" class="w-full flex items-center justify-between mobile-item" type="button">
              <span>eCampus</span>
              <svg :class="{ 'rotate-180': digitalOpen }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.44l3.71-4.21a.75.75 0 111.12 1L10.56 13a.75.75 0 01-1.12 0L5.21 8.24a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <div v-show="digitalOpen" class="px-2 pb-2">
              <a href="https://stmark.sc.ug/elearning/" target="_blank" rel="noopener noreferrer" class="mobile-sub-item">eLearning</a>
              <a href="http://smacondecides.center4webpresence.com/" target="_blank" rel="noopener noreferrer" class="mobile-sub-item">eVoting System</a>
              <a href="https://stmark.sc.ug/laptop_tracking_system/auth/login.php" target="_blank" rel="noopener noreferrer" class="mobile-sub-item">Cyber Monitor</a>
              <a href="https://stmark.sc.ug/concerting/" target="_blank" rel="noopener noreferrer" class="mobile-sub-item">eTicketing</a>
            </div>
          </div>

          <div class="mobile-section">
            <button @click="toggleAcademicsMobile" class="w-full flex items-center justify-between mobile-item" type="button">
              <span>Academics</span>
              <svg :class="{ 'rotate-180': academicsMobileOpen }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.44l3.71-4.21a.75.75 0 111.12 1L10.56 13a.75.75 0 01-1.12 0L5.21 8.24a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <div v-show="academicsMobileOpen" class="px-2 pb-2">
              <Link @click="closeMobile" :href="safeRoute('academics.curriculum')" class="mobile-sub-item">Curriculum</Link>
              <Link @click="closeMobile" :href="safeRoute('academics.co-curricular')" class="mobile-sub-item">Co-Curricular</Link>
              <Link @click="closeMobile" :href="safeRoute('performance.index')" class="mobile-sub-item">Performance</Link>
              <Link @click="closeMobile" :href="safeRoute('academics.high-achievers')" class="mobile-sub-item">High Achievers</Link>
            </div>
          </div>

          <div class="mobile-section">
            <button @click="toggleEmpowermentMobile" class="w-full flex items-center justify-between mobile-item" type="button">
              <span>Empowerment</span>
              <svg :class="{ 'rotate-180': empowermentMobileOpen }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.44l3.71-4.21a.75.75 0 111.12 1L10.56 13a.75.75 0 01-1.12 0L5.21 8.24a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

          <div v-show="empowermentMobileOpen" class="px-2 pb-2">
                  <Link @click="closeMobile" :href="safeRoute('empowerment.chaplaincy')" class="mobile-sub-item">
                    Chaplaincy
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.mentorship')" class="mobile-sub-item">
                    Mentorship Programme
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.girl-boy-talk')" class="mobile-sub-item">
                    Girl-Boy Talk
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.inspiration-night')" class="mobile-sub-item">
                    Inspiration Night
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.smosa-alumni')" class="mobile-sub-item">
                    SMOSA Alumni
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.smosa-feedback')" class="mobile-sub-item">
                    SMOSA Feedback
                  </Link>

                  <Link @click="closeMobile" :href="safeRoute('empowerment.christmas-cantata')" class="mobile-sub-item">
                    Christmas Cantata
                  </Link>
                </div>
          </div>

          <Link @click="closeMobile" :href="safeRoute('contact')" class="mobile-item">
            <span>Contact</span>
          </Link>
        </nav>
      </aside>
    </transition>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, onBeforeUnmount, ref, watch, nextTick } from 'vue'

const page = usePage()
const latestPosts = computed(() => page.props.latestPosts ?? [])

/* UI state */
const mobileOpen = ref(false)
const aboutOpen = ref(false)
const digitalOpen = ref(false)
const academicsMobileOpen = ref(false)
const exploreMobileOpen = ref(false)
const empowermentMobileOpen = ref(false)

/* ticker logic */
const currentPostIndex = ref(0)
const rotationDuration = 4000
let rotateInterval = null
const isPaused = ref(false)

const currentPost = computed(() => {
  if (!latestPosts.value || latestPosts.value.length === 0) return null
  const idx = currentPostIndex.value % latestPosts.value.length
  return latestPosts.value[idx]
})

const currentPostKey = computed(() => `post-${currentPostIndex.value}-${currentPost.value?.id ?? 'none'}`)

function startRotation() {
  stopRotation()

  if (latestPosts.value.length > 0) {
    currentPostIndex.value = currentPostIndex.value % latestPosts.value.length
  } else {
    currentPostIndex.value = 0
  }

  rotateInterval = setInterval(() => {
    if (latestPosts.value.length > 0 && !isPaused.value) {
      currentPostIndex.value = (currentPostIndex.value + 1) % latestPosts.value.length
    }
  }, rotationDuration)
}

function stopRotation() {
  if (rotateInterval) {
    clearInterval(rotateInterval)
    rotateInterval = null
  }
}

function pauseRotation() {
  isPaused.value = true
}

function resumeRotation() {
  if (!isPaused.value) return
  isPaused.value = false
}

/* react to posts updating from server */
watch(latestPosts, (newList) => {
  if (!newList || newList.length === 0) {
    currentPostIndex.value = 0
  } else {
    currentPostIndex.value = currentPostIndex.value % newList.length
  }
  startRotation()
})

/* mobile menu functions */
function openMobile() {
  mobileOpen.value = true
  aboutOpen.value = false
  digitalOpen.value = false
  academicsMobileOpen.value = false
  exploreMobileOpen.value = false
  empowermentMobileOpen.value = false
  document.documentElement.style.overflow = 'hidden'
  document.body.style.overflow = 'hidden'
}

function closeMobile() {
  mobileOpen.value = false
  aboutOpen.value = false
  digitalOpen.value = false
  academicsMobileOpen.value = false
  exploreMobileOpen.value = false
  empowermentMobileOpen.value = false
  document.documentElement.style.overflow = ''
  document.body.style.overflow = ''
}

function toggleAboutMobile() {
  aboutOpen.value = !aboutOpen.value
}

function toggleDigitalMobile() {
  digitalOpen.value = !digitalOpen.value
}

function toggleAcademicsMobile() {
  academicsMobileOpen.value = !academicsMobileOpen.value
}

function toggleExploreMobile() {
  exploreMobileOpen.value = !exploreMobileOpen.value
}

function toggleEmpowermentMobile() {
  empowermentMobileOpen.value = !empowermentMobileOpen.value
}

/* close on ESC */
function handleKeydown(event) {
  if (event.key === 'Escape' && mobileOpen.value) {
    closeMobile()
  }
}

/* ========== Header measurement ========== */
const headerRef = ref(null)
const headerHeight = ref(0)

function measureHeader() {
  if (!headerRef.value) return
  headerHeight.value = Math.ceil(headerRef.value.getBoundingClientRect().height)
}

/* ========== Ticker show/hide on scroll ========== */
const showTicker = ref(true)
const SCROLL_HIDE_THRESHOLD = 80

function handleScroll() {
  showTicker.value = window.scrollY <= SCROLL_HIDE_THRESHOLD
}

/* lifecycle */
let resizeObserver = null

onMounted(async () => {
  if (latestPosts.value.length > 0) {
    currentPostIndex.value = currentPostIndex.value % latestPosts.value.length
  }
  startRotation()

  await nextTick()
  measureHeader()

  if (window.ResizeObserver) {
    resizeObserver = new ResizeObserver(() => measureHeader())
    if (headerRef.value) resizeObserver.observe(headerRef.value)
  } else {
    window.addEventListener('resize', measureHeader)
  }

  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('keydown', handleKeydown)
  handleScroll()
})

onBeforeUnmount(() => {
  stopRotation()
  document.documentElement.style.overflow = ''
  document.body.style.overflow = ''

  if (resizeObserver && headerRef.value) resizeObserver.disconnect()
  else window.removeEventListener('resize', measureHeader)

  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('keydown', handleKeydown)
})

/* ========== Safe route helper ========== */
function safeRoute(name, params = undefined) {
  try {
    if (typeof window !== 'undefined' && typeof window.route === 'function') {
      return window.route(name, params)
    }
    if (typeof route === 'function') {
      return route(name, params)
    }
    return '#'
  } catch (err) {
    console.warn(`Ziggy: missing route "${name}" — returning "#" instead`, err)
    return '#'
  }
}
</script>

<style scoped>
.tooltip {
  position: absolute;
  bottom: -28px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 11px;
  background: rgba(0, 0, 0, 0.85);
  color: white;
  padding: 2px 8px;
  border-radius: 6px;
  opacity: 0;
  transition: 0.3s;
  white-space: nowrap;
}

.group:hover .tooltip {
  opacity: 1;
}

/* dropdown */
.dropdown-item {
  @apply block px-4 py-3 text-gray-800 font-medium hover:bg-yellow-200 transition rounded-md;
}

/* desktop nav */
.nav-link {
  @apply text-blue-900 font-extrabold 
         text-sm md:text-sm lg:text-base xl:text-lg 2xl:text-xl
         tracking-wide px-1 lg:px-2 xl:px-3 py-2 rounded-md transition duration-300;
}
.nav-link:hover {
  @apply bg-yellow-300 text-blue-900;
}

/* mobile sidebar */
.mobile-item {
  @apply flex items-center justify-between w-full text-white font-semibold px-4 py-3 rounded-xl hover:bg-white/10 transition;
}
.mobile-sub-item {
  @apply block text-blue-100 text-sm px-4 py-2 rounded-lg hover:bg-white/10 transition;
}
.mobile-section {
  @apply rounded-2xl border border-white/10 bg-white/5 overflow-hidden;
}

/* fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 240ms ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}

/* ticker */
.latest-moving-link {
  color: #ffd400;
  font-weight: 800;
  font-size: 1.05rem;
  letter-spacing: 0.3px;
  text-shadow:
    0 1px 0 rgba(0, 0, 0, 0.28),
    0 0 10px rgba(255, 212, 64, 0.18);
  white-space: nowrap;
  will-change: transform, opacity;
  transition: transform 0.2s ease, text-shadow 0.2s ease;
  outline: none;
}
.latest-moving-link:hover,
.latest-moving-link:focus {
  transform: translateY(-2px);
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-decoration-color: #ffd400;
}

/* ticker animations */
.ticker-enter-from {
  transform: translateY(30px);
  opacity: 0;
}
.ticker-enter-active {
  transition: transform 0.6s ease, opacity 0.6s ease;
}
.ticker-enter-to {
  transform: translateY(0);
  opacity: 1;
}
.ticker-leave-from {
  transform: translateY(0);
  opacity: 1;
}
.ticker-leave-active {
  transition: transform 0.6s ease, opacity 0.6s ease;
}
.ticker-leave-to {
  transform: translateY(-30px);
  opacity: 0;
}

/* sidebar drawer transition */
.drawer-enter-active {
  transition:
    transform 0.24s cubic-bezier(0.2, 0.9, 0.3, 1),
    opacity 0.24s ease;
}
.drawer-leave-active {
  transition:
    transform 0.18s ease,
    opacity 0.18s ease;
}
.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}
.drawer-enter-to,
.drawer-leave-from {
  transform: translateX(0);
  opacity: 1;
}

/* accessibility focus */
a:focus,
button:focus {
  outline: 3px solid rgba(252, 211, 77, 0.18);
  outline-offset: 2px;
  border-radius: 6px;
}
</style>