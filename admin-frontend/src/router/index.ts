import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

function page(path: string) {
  return () => import(`../pages/admin/${path}.vue`)
}

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
        { path: 'posts', name: 'Posts', component: page('Posts/List'), meta: { title: 'Posts' } },
        { path: 'media', name: 'Media', component: page('Media/List'), meta: { title: 'Media Upload' } },
        { path: 'staff', name: 'Staff', component: page('Staff/List'), meta: { title: 'Manage Staff' } },
        { path: 'board-members', name: 'BoardMembers', component: page('BoardMembers/List'), meta: { title: 'Board Members' } },
        { path: 'applications', name: 'Applications', component: page('JobApplications/List'), meta: { title: 'Job Applications' } },
        { path: 'contacts', name: 'Contacts', component: page('Contacts/List'), meta: { title: 'Contact Messages' } },
        {
          path: 'fee-structures',
          name: 'FeeStructures',
          component: page('FeeStructures/List'),
          meta: { title: 'Fee, Personal Needs, School Rules & Calendar' },
        },
        { path: 'gallery', name: 'Gallery', component: page('Gallery/List'), meta: { title: 'Update Gallery' } },
        { path: 'clubs', name: 'Clubs', component: page('Clubs/List'), meta: { title: 'Manage Clubs' } },
        { path: 'co-curricular', name: 'CoCurricular', component: page('CoCurricular/List'), meta: { title: 'Manage Co-Curriculars' } },
        { path: 'chaplaincy', name: 'Chaplaincy', component: page('Chaplaincy/List'), meta: { title: 'Manage Chaplaincy' } },
        { path: 'mentorship', name: 'Mentorship', component: page('Mentorship/List'), meta: { title: 'Manage Mentorship' } },
        { path: 'performances', name: 'Performances', component: page('Performances/List'), meta: { title: 'Performance & Circulars' } },
        { path: 'high-achievers', name: 'HighAchievers', component: page('HighAchievers/List'), meta: { title: 'Manage High Achievers' } },
        {
          path: 'student-leadership',
          name: 'StudentLeadership',
          component: page('StudentLeadership/List'),
          meta: { title: 'Student Leadership' },
        },
        { path: 'girlboytalk', name: 'GirlBoyTalk', component: page('GirlBoyTalk/List'), meta: { title: 'Girl - Boy Talk' } },
        { path: 'inspiration', name: 'InspirationNight', component: page('InspirationNight/List'), meta: { title: 'Inspiration Night' } },
        {
          path: 'christmas-cantata',
          name: 'ChristmasCantata',
          component: page('ChristmasCantata/List'),
          meta: { title: 'Manage Cantata' },
        },
        { path: 'smosa', name: 'SmosaAlumni', component: page('SmosaAlumni/List'), meta: { title: 'SMOSA Alumni' } },
        { path: 'smosafeedback', name: 'SmosaFeedback', component: page('SmosaFeedback/List'), meta: { title: 'SMOSA Feedback' } },
        { path: 'campus-voices', name: 'CampusVoices', component: page('CampusVoices/List'), meta: { title: 'Campus Voices' } },
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
