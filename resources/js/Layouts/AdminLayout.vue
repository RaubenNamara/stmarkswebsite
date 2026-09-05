<template>
  <div class="min-h-screen flex bg-gray-100 text-gray-900">

    <!-- Mobile overlay -->
    <transition name="fade">
      <div
        v-if="mobileOpen"
        class="fixed inset-0 z-30 bg-black/40 lg:hidden"
        @click="mobileOpen = false"
      />
    </transition>

    <!-- SIDEBAR -->
    <aside
      :class="[
        'z-40 fixed inset-y-0 left-0 transform w-64 bg-blue-950 text-white flex flex-col shadow-lg transition-transform duration-300',
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >

      <!-- Brand -->
      <div class="px-6 py-6 border-b border-blue-800">
        <h1 class="text-2xl font-extrabold tracking-tight">Admin Panel</h1>
        <p class="text-sm text-blue-300 mt-1">St. Mark’s College</p>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-6 overflow-y-auto space-y-2">

        <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wide">
          Main
        </p>

        <Link
          v-for="item in navItems"
          :key="item.key"
          :href="safeRoute(item.route)"
          :class="navClass(isActive(item))"
        >
          <span class="w-6 h-6 flex items-center justify-center">
            {{ item.icon }}
          </span>

          <span class="truncate">
            {{ item.label }}
          </span>
        </Link>

      </nav>

      <!-- Logout -->
      <div class="p-4 border-t border-blue-800">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="w-full bg-red-600 hover:bg-red-700 py-2 rounded-lg text-white font-semibold"
        >
          Logout
        </Link>
      </div>

    </aside>


    <!-- MAIN CONTENT -->
    <div class="flex-1 lg:pl-64 min-h-screen flex flex-col">

      <!-- TOP BAR -->
      <header class="w-full bg-white border-b border-gray-200">

        <div class="max-w-7xl mx-auto px-4 flex items-center h-16 gap-4">

          <!-- Mobile menu -->
          <button
            class="lg:hidden p-2 rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
            @click="mobileOpen = !mobileOpen"
          >
            ☰
          </button>

          <!-- Page header -->
          <div class="flex-1 flex items-center gap-4">

            <slot name="header">
              <h2 class="text-lg font-semibold text-gray-800">
                Dashboard
              </h2>
            </slot>

            <!-- Add Button -->
            <div v-if="showActionButton" class="ml-4">
              <Link
                :href="safeRoute(actionRoute)"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow"
              >
                <span class="text-lg leading-none">＋</span>
                <span>{{ actionLabel }}</span>
              </Link>
            </div>

          </div>

          <!-- User -->
          <div class="hidden sm:block text-sm text-gray-600">
            Signed in as
            <span class="font-medium text-gray-800">
              {{ userName }}
            </span>
          </div>

        </div>

      </header>


      <!-- PAGE CONTENT -->
      <main class="flex-1 bg-gray-50">

        <div class="max-w-7xl mx-auto px-4 py-8">
          <slot />
        </div>

      </main>

    </div>

  </div>
</template>


<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const mobileOpen = ref(false)

/*
|--------------------------------------------------------------------------
| Navigation Items
|--------------------------------------------------------------------------
*/

const navItems = [

  { key: 'dashboard', label: 'Slide Show', route: 'dashboard', icon: '📊' },

  { key: 'news', label: 'Latest News', route: 'admin.news.index', icon: '📰' },

  { key: 'posts', label: 'Posts', route: 'admin.posts.index', icon: '📝' },

  { key: 'media', label: 'Media Upload', route: 'admin.media.index', icon: '🎬' },

  { key: 'staff', label: 'Manage Staff', route: 'admin.staff.index', icon: '👩‍🏫' },

  { key: 'board-members', label: 'Board Members', route: 'admin.board-members.index', icon: '🏛️' },

  { key: 'applications', label: 'Job Applications', route: 'admin.applications.index', icon: '📁' },

  // <-- NEW: Contact Messages button added to sidebar
  { key: 'contacts', label: 'Contact Messages', route: 'admin.contacts.index', icon: '✉️' },
  // <-- end new

  { key: 'fee-structures', label: 'Fee, Personal needs, School Rules & Calendar', route: 'admin.fee-structures.index', icon: '💰' },

  { key: 'gallery', label: 'Update Gallery', route: 'admin.gallery.index', icon: '🖼️' },

  { key: 'clubs', label: 'Manage Clubs', route: 'admin.clubs.index', icon: '🎯' },

  { key: 'co-curricular', label: 'Manage Co-Curriculars', route: 'admin.co-curricular.index', icon: '🎭' },

  { key: 'chaplaincy', label: 'Manage Chaplaincy', route: 'admin.chaplaincy.index', icon: '⛪' },

  { key: 'mentorship', label: 'Manage Mentorship', route: 'admin.mentorship.index', icon: '🎓' },

  { key: 'performances', label: 'Performance & Circulars', route: 'admin.performances.index', icon: '📄' },

  { key: 'high-achievers', label: 'Manage High Achievers', route: 'admin.high-achievers.index', icon: '🏆' },

  { key: 'student-leadership', label: 'Student Leadership', route: 'admin.student-leadership.index', icon: '👑' },

  { key: 'girlboytalk', label: 'Girl - Boy Talk', route: 'admin.girlboytalk.index', icon: '👫' },

  { key: 'inspiration', label: 'Inspiration Night', route: 'admin.inspiration.index', icon: '✨' },

  /* NEW MENU */
  { key: 'christmas-cantata', label: 'Manage Cantata', route: 'admin.christmas-cantata.index', icon: '🎄' },

  { key: 'smosa', label: 'SMOSA Alumni', route: 'admin.smosa.index', icon: '🎓' },

  { key: 'smosafeedback', label: 'SMOSA Feedback', route: 'admin.smosa-feedback.index', icon: '💬' },

  { key: 'campus-voices', label: 'Campus Voices', route: 'admin.campus-voices.index', icon: '🎤' }

]


/*
|--------------------------------------------------------------------------
| User Name
|--------------------------------------------------------------------------
*/

const userName = computed(() =>
  page.props?.auth?.user?.name ?? 'Admin'
)


/*
|--------------------------------------------------------------------------
| Safe Route Helper
|--------------------------------------------------------------------------
*/

function safeRoute(name) {

  try {
    if (typeof route === 'function') {
      return route(name)
    }
  } catch (e) {}

  const fallback = {

    'dashboard': '/dashboard',

    'admin.news.index': '/admin/news',

    'admin.posts.index': '/admin/posts',

    'admin.media.index': '/admin/media',

    'admin.staff.index': '/admin/staff',

    'admin.board-members.index': '/admin/board-members',

    'admin.applications.index': '/admin/applications',

    // <-- NEW: fallback for contacts route
    'admin.contacts.index': '/admin/contacts',
    // <-- end new

    'admin.fee-structures.index': '/admin/fee-structures',

    'admin.gallery.index': '/admin/gallery',

    'admin.clubs.index': '/admin/clubs',

    'admin.co-curricular.index': '/admin/co-curricular',

    'admin.chaplaincy.index': '/admin/chaplaincy',

    'admin.mentorship.index': '/admin/mentorship',
    'admin.mentorship.create': '/admin/mentorship/create',

    'admin.girlboytalk.index': '/admin/girlboytalk',
    'admin.girlboytalk.create': '/admin/girlboytalk/create',

    'admin.performances.index': '/admin/performances',

    'admin.high-achievers.index': '/admin/high-achievers',

    'admin.student-leadership.index': '/admin/student-leadership',

    'admin.inspiration.index': '/admin/inspiration',
    'admin.inspiration.create': '/admin/inspiration/create',

    /* CHRISTMAS CANTATA */
    'admin.christmas-cantata.index': '/admin/christmas-cantata',
    'admin.christmas-cantata.create': '/admin/christmas-cantata/create',

    /* SMOSA */
    'admin.smosa.index': '/admin/smosa',
    'admin.smosa.create': '/admin/smosa/create',

    /* SMOSA Feedback */
    'admin.smosa-feedback.index': '/admin/smosa-feedback',

    /* Campus Voices */
    'admin.campus-voices.index': '/admin/campus-voices',
    'admin.campus-voices.create': '/admin/campus-voices/create',

    'logout': '/logout'
  }

  return fallback[name] ?? '#'
}


/*
|--------------------------------------------------------------------------
| Active Menu
|--------------------------------------------------------------------------
*/

function isActive(item) {
  const comp = (page.component || '').toLowerCase()
  return comp.includes(item.key)
}


/*
|--------------------------------------------------------------------------
| Navigation Style
|--------------------------------------------------------------------------
*/

function navClass(active) {

  return [

    'flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150',

    active
      ? 'bg-blue-800 text-white shadow'
      : 'text-blue-200 hover:bg-blue-900 hover:text-white'

  ].join(' ')
}


/*
|--------------------------------------------------------------------------
| Action Button Map
|--------------------------------------------------------------------------
*/

const actionMap = {

  girlboytalk: { label: 'Add Talk', route: 'admin.girlboytalk.create' },

  mentorship: { label: 'Add Mentorship', route: 'admin.mentorship.create' },

  posts: { label: 'Add Post', route: 'admin.posts.create' },

  media: { label: 'Upload Media', route: 'admin.media.create' },

  inspiration: { label: 'Add Inspiration Night', route: 'admin.inspiration.create' },

  /* NEW: Christmas Cantata */
  'christmas-cantata': { label: 'Add Cantata', route: 'admin.christmas-cantata.create' },

  /* NEW */
  smosa: { label: 'Add SMOSA Alumni', route: 'admin.smosa.create' },

  /* Campus Voices */
  'campus-voices': { label: 'Add Article', route: 'admin.campus-voices.create' }
}

const currentComp = computed(() => (page.component || '').toLowerCase())

const matchedKey = computed(() => {
  for (const key of Object.keys(actionMap)) {
    if (currentComp.value.includes(key)) return key
  }
  return null
})

const showActionButton = computed(() => matchedKey.value !== null)

const actionLabel = computed(() => {
  return matchedKey.value ? actionMap[matchedKey.value].label : ''
})

const actionRoute = computed(() => {
  return matchedKey.value ? actionMap[matchedKey.value].route : ''
})

</script>


<style scoped>

.fade-enter-active,
.fade-leave-active {
  transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

aside nav::-webkit-scrollbar {
  width: 8px;
}

aside nav::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.08);
  border-radius: 999px;
}

@media (min-width: 1024px) {
  .lg\:pl-64 {
    padding-left: 16rem;
  }
}

</style>