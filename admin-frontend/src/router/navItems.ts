export interface NavItem {
  key: string
  label: string
  path: string
  icon: string
}

/**
 * Mirrors the old Laravel admin's AdminLayout.vue sidebar exactly (labels, icons, order) - shared
 * by MainLayout (renders the sidebar) and ComingSoon (looks up a label for its :key param) so
 * there's one source of truth instead of two copies drifting apart.
 */
export const navItems: NavItem[] = [
  { key: 'dashboard', label: 'Slide Show', path: '/dashboard', icon: '📊' },
  { key: 'news', label: 'Latest News', path: '/news', icon: '📰' },
  { key: 'posts', label: 'Posts', path: '/posts', icon: '📝' },
  { key: 'media', label: 'Media Upload', path: '/media', icon: '🎬' },
  { key: 'staff', label: 'Manage Staff', path: '/staff', icon: '👩‍🏫' },
  { key: 'board-members', label: 'Board Members', path: '/board-members', icon: '🏛️' },
  { key: 'applications', label: 'Job Applications', path: '/applications', icon: '📁' },
  { key: 'contacts', label: 'Contact Messages', path: '/contacts', icon: '✉️' },
  { key: 'fee-structures', label: 'Fee, Personal needs, School Rules & Calendar', path: '/fee-structures', icon: '💰' },
  { key: 'gallery', label: 'Update Gallery', path: '/gallery', icon: '🖼️' },
  { key: 'clubs', label: 'Manage Clubs', path: '/clubs', icon: '🎯' },
  { key: 'co-curricular', label: 'Manage Co-Curriculars', path: '/co-curricular', icon: '🎭' },
  { key: 'chaplaincy', label: 'Manage Chaplaincy', path: '/chaplaincy', icon: '⛪' },
  { key: 'mentorship', label: 'Manage Mentorship', path: '/mentorship', icon: '🎓' },
  { key: 'performances', label: 'Performance & Circulars', path: '/performances', icon: '📄' },
  { key: 'high-achievers', label: 'Manage High Achievers', path: '/high-achievers', icon: '🏆' },
  { key: 'student-leadership', label: 'Student Leadership', path: '/student-leadership', icon: '👑' },
  { key: 'girlboytalk', label: 'Girl - Boy Talk', path: '/girlboytalk', icon: '👫' },
  { key: 'inspiration', label: 'Inspiration Night', path: '/inspiration', icon: '✨' },
  { key: 'christmas-cantata', label: 'Manage Cantata', path: '/christmas-cantata', icon: '🎄' },
  { key: 'smosa', label: 'SMOSA Alumni', path: '/smosa', icon: '🎓' },
  { key: 'smosafeedback', label: 'SMOSA Feedback', path: '/smosafeedback', icon: '💬' },
  { key: 'campus-voices', label: 'Campus Voices', path: '/campus-voices', icon: '🎤' },
]
