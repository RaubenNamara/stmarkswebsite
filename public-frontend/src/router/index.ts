import type { RouteRecordRaw } from 'vue-router'

/** Config passed to the one shared MediaGridPage.vue, mirroring public-site's listings/media-grid.php pattern. */
function mediaGrid(config: {
  endpoint: string
  collectionKey: string
  pageTitle: string
  titleField: string
  textFields: Record<string, string>
  imageField?: string
  videoField?: string
}) {
  return {
    component: () => import('../pages/listings/MediaGridPage.vue'),
    props: () => config,
  }
}

function digitalCampus(title: string, description: string) {
  return {
    component: () => import('../pages/DigitalCampus.vue'),
    props: () => ({ title, description }),
  }
}

export const routes: RouteRecordRaw[] = [
  { path: '/', component: () => import('../pages/Home.vue') },

  { path: '/news', component: () => import('../pages/news/Index.vue') },
  { path: '/news/:slug', component: () => import('../pages/news/Show.vue'), props: true },

  { path: '/academics', component: () => import('../pages/academics/Index.vue') },
  { path: '/admissions', component: () => import('../pages/Admissions.vue') },
  { path: '/empowerment-programmes', component: () => import('../pages/EmpowermentProgrammes.vue') },
  { path: '/core-values', component: () => import('../pages/CoreValues.vue') },
  { path: '/school-anthem', component: () => import('../pages/Anthem.vue') },
  { path: '/college-name', component: () => import('../pages/CollegeName.vue') },
  { path: '/headteacher', component: () => import('../pages/Headteacher.vue') },
  { path: '/director1', component: () => import('../pages/Director1.vue') },
  { path: '/director2', component: () => import('../pages/Director2.vue') },

  { path: '/elearning', ...digitalCampus('eLearning', 'Access class notes, assignments and virtual lessons through the eSpace learning portal.') },
  { path: '/evoting', ...digitalCampus('eVoting', 'Secure digital voting for student leadership and school elections.') },
  { path: '/cybermonitor', ...digitalCampus('CyberMonitor', "Keeping our students safe online through the school's digital monitoring programme.") },
  { path: '/econcerting', ...digitalCampus('eConcerting', 'Livestreamed and recorded school concerts, performances and events.') },

  { path: '/academics/curriculum', component: () => import('../pages/academics/Curriculum.vue') },
  { path: '/academics/uneb-results', component: () => import('../pages/academics/UnebResults.vue') },
  { path: '/academics/circulars', component: () => import('../pages/academics/Circulars.vue') },
  { path: '/academics/school-calendar', component: () => import('../pages/academics/SchoolCalendar.vue') },

  { path: '/explore/career', component: () => import('../pages/explore/Career.vue') },
  { path: '/explore/personal-needs', component: () => import('../pages/explore/PersonalNeeds.vue') },
  { path: '/explore/uniform', component: () => import('../pages/explore/Uniform.vue') },

  { path: '/board-members', component: () => import('../pages/BoardMembers.vue') },
  { path: '/academics/co-curricular', component: () => import('../pages/academics/CoCurricular.vue') },
  {
    path: '/academics/high-achievers',
    component: () => import('../pages/academics/HighAchievers.vue'),
  },
  { path: '/empowerment/mentorship', component: () => import('../pages/empowerment/Mentorship.vue') },
  { path: '/empowerment/girl-boy-talk', component: () => import('../pages/empowerment/GirlBoyTalk.vue') },
  { path: '/empowerment/inspiration-night', component: () => import('../pages/empowerment/InspirationNight.vue') },
  { path: '/empowerment/smosa-alumni', component: () => import('../pages/empowerment/SmosaAlumni.vue') },
  { path: '/empowerment/christmas-cantata', component: () => import('../pages/empowerment/ChristmasCantata.vue') },
  { path: '/empowerment/chaplaincy', component: () => import('../pages/empowerment/Chaplaincy.vue') },
  {
    path: '/explore/student-leadership',
    ...mediaGrid({
      endpoint: '/student-leadership',
      collectionKey: 'student_leadership',
      pageTitle: 'Student Leadership',
      titleField: 'title',
      textFields: { content: 'Content' },
      imageField: 'image_path_url',
      videoField: 'video_path_url',
    }),
  },

  { path: '/staff', component: () => import('../pages/Staff.vue') },
  { path: '/clubs', component: () => import('../pages/clubs/Index.vue') },
  { path: '/clubs/:slug', component: () => import('../pages/clubs/Show.vue'), props: true },
  { path: '/campus-voices', component: () => import('../pages/campus-voices/Index.vue') },
  { path: '/explore/campus-voices', component: () => import('../pages/campus-voices/Index.vue') },
  { path: '/campus-voices/:slug', component: () => import('../pages/campus-voices/Show.vue'), props: true },
  { path: '/posts', component: () => import('../pages/posts/Index.vue') },
  { path: '/posts/:slug', component: () => import('../pages/posts/Show.vue'), props: true },

  { path: '/explore/gallery', component: () => import('../pages/Gallery.vue') },

  { path: '/fee-structures', component: () => import('../pages/FeeStructures.vue') },
  { path: '/explore/fees', component: () => import('../pages/FeeStructures.vue') },
  { path: '/performance', component: () => import('../pages/Performances.vue') },

  { path: '/contact', component: () => import('../pages/forms/Contact.vue') },
  { path: '/apply', component: () => import('../pages/forms/Apply.vue') },
  { path: '/smosa-feedback', component: () => import('../pages/forms/SmosaFeedback.vue') },

  { path: '/:pathMatch(.*)*', component: () => import('../pages/errors/NotFound.vue') },
]
