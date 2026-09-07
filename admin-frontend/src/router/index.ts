import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      component: () => import('@/layouts/AuthLayout.vue'),
      children: [{ path: '', name: 'Login', component: () => import('@/pages/auth/Login.vue') }],
    },
    {
      path: '/',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', name: 'Dashboard', component: () => import('@/pages/admin/Dashboard.vue'), meta: { title: 'Slide Show' } },
        { path: 'news', name: 'News', component: () => import('@/pages/admin/News/List.vue'), meta: { title: 'Latest News' } },
        { path: 'posts', name: 'Posts', component: () => import('@/pages/admin/Posts/List.vue'), meta: { title: 'Posts' } },
        { path: 'media', name: 'Media', component: () => import('@/pages/admin/Media/List.vue'), meta: { title: 'Media Upload' } },
        { path: 'staff', name: 'Staff', component: () => import('@/pages/admin/Staff/List.vue'), meta: { title: 'Manage Staff' } },
        {
          path: 'board-members',
          name: 'BoardMembers',
          component: () => import('@/pages/admin/BoardMembers/List.vue'),
          meta: { title: 'Board Members' },
        },
        {
          path: 'applications',
          name: 'Applications',
          component: () => import('@/pages/admin/JobApplications/List.vue'),
          meta: { title: 'Job Applications' },
        },
        { path: 'contacts', name: 'Contacts', component: () => import('@/pages/admin/Contacts/List.vue'), meta: { title: 'Contact Messages' } },
        {
          path: 'fee-structures',
          name: 'FeeStructures',
          component: () => import('@/pages/admin/FeeStructures/List.vue'),
          meta: { title: 'Fee, Personal Needs, School Rules & Calendar' },
        },
        { path: 'gallery', name: 'Gallery', component: () => import('@/pages/admin/Gallery/List.vue'), meta: { title: 'Update Gallery' } },
        { path: 'clubs', name: 'Clubs', component: () => import('@/pages/admin/Clubs/List.vue'), meta: { title: 'Manage Clubs' } },
        {
          path: 'co-curricular',
          name: 'CoCurricular',
          component: () => import('@/pages/admin/CoCurricular/List.vue'),
          meta: { title: 'Manage Co-Curriculars' },
        },
        {
          path: 'chaplaincy',
          name: 'Chaplaincy',
          component: () => import('@/pages/admin/Chaplaincy/List.vue'),
          meta: { title: 'Manage Chaplaincy' },
        },
        {
          path: 'mentorship',
          name: 'Mentorship',
          component: () => import('@/pages/admin/Mentorship/List.vue'),
          meta: { title: 'Manage Mentorship' },
        },
        {
          path: 'performances',
          name: 'Performances',
          component: () => import('@/pages/admin/Performances/List.vue'),
          meta: { title: 'Performance & Circulars' },
        },
        {
          path: 'high-achievers',
          name: 'HighAchievers',
          component: () => import('@/pages/admin/HighAchievers/List.vue'),
          meta: { title: 'Manage High Achievers' },
        },
        {
          path: 'student-leadership',
          name: 'StudentLeadership',
          component: () => import('@/pages/admin/StudentLeadership/List.vue'),
          meta: { title: 'Student Leadership' },
        },
        {
          path: 'girlboytalk',
          name: 'GirlBoyTalk',
          component: () => import('@/pages/admin/GirlBoyTalk/List.vue'),
          meta: { title: 'Girl - Boy Talk' },
        },
        {
          path: 'inspiration',
          name: 'InspirationNight',
          component: () => import('@/pages/admin/InspirationNight/List.vue'),
          meta: { title: 'Inspiration Night' },
        },
        {
          path: 'christmas-cantata',
          name: 'ChristmasCantata',
          component: () => import('@/pages/admin/ChristmasCantata/List.vue'),
          meta: { title: 'Manage Cantata' },
        },
        {
          path: 'smosa',
          name: 'SmosaAlumni',
          component: () => import('@/pages/admin/SmosaAlumni/List.vue'),
          meta: { title: 'SMOSA Alumni' },
        },
        {
          path: 'smosafeedback',
          name: 'SmosaFeedback',
          component: () => import('@/pages/admin/SmosaFeedback/List.vue'),
          meta: { title: 'SMOSA Feedback' },
        },
        {
          path: 'campus-voices',
          name: 'CampusVoices',
          component: () => import('@/pages/admin/CampusVoices/List.vue'),
          meta: { title: 'Campus Voices' },
        },
        {
          path: 'coming-soon/:key',
          name: 'ComingSoon',
          component: () => import('@/pages/admin/ComingSoon.vue'),
          meta: { title: 'Coming soon' },
        },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/login' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (auth.user === null && sessionStorage.getItem('user')) {
    auth.restoreFromStorage()
  }

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)

  if (requiresAuth && !auth.isAuthenticated) {
    return { name: 'Login' }
  }

  if (to.name === 'Login' && auth.isAuthenticated) {
    return { path: '/dashboard' }
  }

  return true
})

export default router
