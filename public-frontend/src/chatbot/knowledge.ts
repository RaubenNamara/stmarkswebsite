/**
 * Knowledge base for the school chatbot.
 *
 * SOURCE OF TRUTH: every fact below was taken from a page of this website (the page is named in
 * the comment above each entry). Nothing here is invented - where the website does not publish a
 * detail (admission requirements, fee amounts, term dates, transport, bursaries ...) the entry
 * says so and sends the visitor to the school office.
 *
 * HOW TO ADD OR CHANGE AN ANSWER
 *   1. Copy an existing entry and give it a new unique `id`.
 *   2. `questions`: 3-6 natural ways a visitor might ask it. The FIRST one is also what a button
 *      click sends, so make it the clearest phrasing.
 *   3. `keywords`: words/phrases that signal the topic. Single words and phrases both work; a
 *      phrase matches when all its words appear in the question (any order, contiguous scores
 *      higher). Spelling variations, plurals and synonyms are folded automatically (see text.ts),
 *      so "fees", "fee" and "tuition" are the same keyword.
 *   4. `weak`: context words that help a little but must not decide on their own ("where", "when").
 *   5. `exclude`: words that mean the question is about something else (e.g. "job" for admissions).
 *   6. `answer`: plain text only (no HTML). Line breaks and "•" bullets are kept as typed.
 *   7. `links`: only pages that exist in router/index.ts, or approved external links (see
 *      links.ts). Internal links are written WITHOUT the /stmarkswebsite base path.
 *   8. `related`: ids of entries to offer as follow-up buttons.
 *   9. Run `npm test` - it checks that links are valid, ids are unique and every example question
 *      still lands on its own entry.
 *
 * If a fact changes on the website (a phone number, office hours ...), change it here too.
 */

import type { ChatLink } from './links.ts'

export type Topic =
  | 'about'
  | 'admissions'
  | 'fees'
  | 'contact'
  | 'location'
  | 'academics'
  | 'activities'
  | 'support'
  | 'life'
  | 'elearning'
  | 'news'
  | 'careers'
  | 'meta'

export interface KnowledgeEntry {
  id: string
  topic: Topic
  /** Shown to maintainers; groups entries in the docs. */
  category: string
  /** Short button label. */
  chip: string
  questions: string[]
  keywords: string[]
  weak?: string[]
  exclude?: string[]
  answer: string
  links?: ChatLink[]
  related?: string[]
  /** Offers a next step; a following "yes" answers with `entryId`. */
  next?: { prompt: string; entryId: string }
}

// ---- Verified contact details (website footer, top bar and Contact page) -------------------------

export const SCHOOL_NAME = "St. Mark's College Namagoma"
/** Names published on the Home / Head Teacher pages. Live staff data (see liveAnswer.ts) takes precedence when available. */
export const DIRECTOR_NAME = 'Omuk. DG Daniel Mukasa Ddamulira'
/** The school has two directors. */
export const DIRECTOR2_NAME = 'Canon Alice Ddamulira'
export const HEADTEACHER_FALLBACK = 'Wabwire Joseph'
export const PHONE_HEADTEACHER = ['+256 393 103 418', '+256 775 831 844', '+256 750 330 158']
export const PHONE_PRO = '+256 772 996 680'
export const EMAIL_DIRECTOR = 'director@stmark.sc.ug'
export const EMAIL_INFO = 'info@stmark.sc.ug'
export const POSTAL = 'P.O. Box 22888, Kampala - Uganda'
export const OFFICE_HOURS = 'Mon - Fri: 7:30 AM - 5:00 PM\n• Sat: 9:00 AM - 1:00 PM\n• Sun: Closed'

const MAPS_PIN = 'https://www.google.com/maps?q=0.287574544131338,32.47190598057264'
const MAPS_DIRECTIONS = 'https://www.google.com/maps/dir/?api=1&destination=0.287574544131338,32.47190598057264'

const L = {
  contactPage: { label: 'Contact page', url: '/contact' },
  callMain: { label: 'Call +256 775 831 844', url: 'tel:+256775831844' },
  emailInfo: { label: 'Email info@stmark.sc.ug', url: 'mailto:info@stmark.sc.ug' },
  emailDirector: { label: 'Email the Director', url: 'mailto:director@stmark.sc.ug' },
  whatsapp: { label: 'WhatsApp the school', url: 'https://wa.me/256775831844' },
  admissionsPage: { label: 'Admissions page', url: '/admissions' },
  applyOnline: { label: 'Apply online', url: 'https://admission.stmark.sc.ug/' },
  feesPage: { label: 'Fees, Rules & Calendar', url: '/fee-structures' },
  academics: { label: 'Academics', url: '/academics' },
  curriculum: { label: 'Curriculum', url: '/academics/curriculum' },
  calendar: { label: 'School calendar', url: '/academics/school-calendar' },
  espace: { label: 'Open eSpace', url: 'https://espace.stmark.sc.ug/' },
  portal: { label: 'School systems portal', url: '/portal' },
} satisfies Record<string, ChatLink>

/** Contact links reused by the fallback and "not published" answers. */
export const CONTACT_LINKS: ChatLink[] = [L.contactPage, L.callMain, L.emailInfo]

export const ENTRIES: KnowledgeEntry[] = [
  // ======================================================================== About the school
  // Source: Home.vue ("Welcome" section), CollegeName.vue
  {
    id: 'about-school',
    topic: 'about',
    category: 'General information',
    chip: 'About the school',
    questions: [
      'Tell me about the school',
      'What is St. Mark\'s College Namagoma?',
      'When was the school founded?',
      'Is the school registered?',
      'How big is the school?',
    ],
    keywords: ['about', 'history', 'founded', 'established', 'overview', 'accredited', 'licensed', 'ministry', 'private school', 'studentcount', 'acres', 'neighbours'],
    weak: ['namagoma'],
    answer:
      "St. Mark's College Namagoma is a high-quality private secondary school, founded in 2003. It is a mixed boarding school offering both Arts and Sciences at O-Level and A-Level.\n\n" +
      '• Campus: 30 acres at Namagoma, along the Kampala-Masaka Road\n' +
      '• Registered with the Ministry of Education and Sports (registration number PSS/S/261)\n' +
      '• UNEB centre number: U1664\n' +
      '• The website reports over 2,000 students across O-Level and A-Level',
    links: [{ label: 'The college name', url: '/college-name' }, { label: 'Core values', url: '/core-values' }],
    related: ['mission-vision', 'leadership', 'location', 'admissions-apply'],
  },
  {
    id: 'boarding',
    topic: 'about',
    category: 'General information',
    chip: 'Boarding',
    questions: [
      'Is it a boarding school?',
      'Do you have boarding?',
      'Is the school mixed or single sex?',
      'Do you take day scholars?',
    ],
    keywords: ['boarding', 'dayscholar', 'mixed', 'coeducational', 'co ed', 'girls only', 'boys only', 'single sex', 'residential', 'sleep'],
    answer:
      "St. Mark's College Namagoma is a mixed boarding school.\n\n" +
      "The website doesn't say whether day scholars are admitted, or give boarding arrangements, so please confirm those details with the school office.",
    links: [L.contactPage, L.callMain],
    related: ['fees', 'admissions-apply', 'facilities'],
  },
  // Source: Home.vue (Mission & Vision)
  {
    id: 'mission-vision',
    topic: 'about',
    category: 'General information',
    chip: 'Mission & vision',
    questions: ['What is the school mission?', 'What is your vision?', 'Mission and vision statement'],
    keywords: ['mission', 'vision', 'aims', 'objectives', 'philosophy', 'purpose'],
    answer:
      'Our mission: To provide top-quality secondary education that nurtures in our students a zest for life, a spirit of enterprise, community service and leadership, through a balanced curriculum that prepares them for an ever-changing world.\n\n' +
      'Our vision: To be a leading academic institution in Uganda and the East African region, producing highly successful and respected individuals in every aspect of life.',
    related: ['motto-values', 'about-school'],
  },
  // Source: Home.vue (Motto / Core Values), CoreValues.vue
  {
    id: 'motto-values',
    topic: 'about',
    category: 'General information',
    chip: 'Motto & core values',
    questions: ['What is the school motto?', 'What are the core values?', 'What does GREET stand for?'],
    keywords: ['motto', 'core value', 'values', 'greet', 'slogan', 'to be not to seem', 'ethos'],
    answer:
      'Motto: "To Be, Not To Seem" - encouraging authenticity, integrity and inner strength over outward appearances.\n\n' +
      'Core values (GREET):\n' +
      '• Godliness\n• Reliability\n• Ethics\n• Excellence\n• Team Work',
    links: [{ label: 'Core values', url: '/core-values' }],
    related: ['mission-vision', 'chaplaincy'],
  },
  // Source: CollegeName.vue
  {
    id: 'college-name',
    topic: 'about',
    category: 'General information',
    chip: 'Why "St. Mark\'s"?',
    questions: ['Why is the school called St. Mark\'s?', 'Meaning of the college name', 'Where does the name come from?'],
    keywords: ['collegename', 'name', 'origin', 'named'],
    answer:
      "According to the college's own explanation, the name was chosen for three reasons:\n" +
      '1. God at the centre - St. Mark\'s writing emphasised actions over words: "Action not words determine our destiny".\n' +
      '2. Cultural and identity value - a standard, easy-to-remember way of naming schools in Uganda.\n' +
      '3. Location and uniqueness - "Namagoma" avoids confusion with any other St. Mark\'s.',
    links: [{ label: 'The college name', url: '/college-name' }],
    related: ['about-school', 'motto-values'],
  },
  // Source: Headteacher.vue, Director1.vue (names as published on the site), Home.vue
  {
    id: 'leadership',
    topic: 'about',
    category: 'General information',
    chip: 'School leadership',
    questions: ['Who is the head teacher?', 'Who is the director of the school?', 'Who runs the school?', 'Tell me about the board of governors'],
    keywords: ['headteacher', 'director', 'board', 'leadership', 'management', 'who runs', 'administration', 'owner', 'proprietor', 'headmaster'],
    exclude: ['job'],
    answer:
      '• Directors: Omuk. DG Daniel Mukasa Ddamulira and Canon Alice Ddamulira\n' +
      '• Head Teacher: Wabwire Joseph\n' +
      '• The College has a Board of Governors made up of educationists, bankers and senior managers.',
    links: [],
    related: ['contact', 'about-school'],
  },
  {
    id: 'staff-list',
    topic: 'about',
    category: 'General information',
    chip: 'Our teachers',
    questions: ['Who are the teachers?', 'Where can I see the school staff?', 'Can I see the list of teachers?', 'Who are the heads of department?'],
    keywords: ['staff', 'teacher', 'tutor', 'faculty', 'heads of department', 'hod'],
    exclude: ['job'],
    answer: 'All teachers and staff are listed, by category, on the Staff page.',
    links: [{ label: 'Staff', url: '/staff' }, { label: 'Board members', url: '/board-members' }],
    related: ['leadership', 'subjects'],
  },
  {
    id: 'anthem',
    topic: 'about',
    category: 'General information',
    chip: 'School anthem',
    questions: ['Where can I find the school anthem?', 'What are the words of the anthem?'],
    keywords: ['anthem', 'song', 'lyrics'],
    answer: 'The school anthem is published on its own page.',
    links: [{ label: 'School anthem', url: '/school-anthem' }],
    related: ['motto-values'],
  },

  // ======================================================================== Admissions
  // Source: Admissions.vue, Portal.vue, Home.vue
  {
    id: 'admissions-apply',
    topic: 'admissions',
    category: 'Admissions',
    chip: 'How to apply',
    questions: [
      'How do I apply for admission?',
      'How do I join the school?',
      'How can I apply?',
      'I want my child to study at St. Mark\'s',
      'Is admission open?',
      'How do I register my child?',
    ],
    keywords: ['admission', 'apply', 'join', 'enrol', 'register', 'application', 'intake', 'new student', 'transfer', 'online application'],
    exclude: ['job', 'login', 'password', 'requirement', 'club', 'choir', 'sport', 'society'],
    answer:
      'Applications for admission are made online: open the online admissions system, complete the application and submit it.',
    links: [L.applyOnline, L.admissionsPage, L.contactPage],
    related: ['admissions-requirements', 'fees', 'contact'],
    next: { prompt: 'Would you like to know about admission requirements?', entryId: 'admissions-requirements' },
  },
  {
    id: 'admissions-requirements',
    topic: 'admissions',
    category: 'Admissions',
    chip: 'Admission requirements',
    questions: [
      'What are the admission requirements?',
      'What documents do I need to apply?',
      'What are the entry requirements?',
      'When does admission close?',
      'Is there an entrance interview or cut-off mark?',
    ],
    keywords: ['requirement', 'admission requirement', 'document', 'deadline', 'closing date', 'cutoff', 'interview', 'entry', 'entrance', 'report form', 'age limit', 'what do i need', 'previous results'],
    exclude: ['job'],
    answer:
      "The admission requirements (documents, entry age, deadlines) aren't published on the website. Please ask the school office:\n" +
      `• Headteacher's Office: ${PHONE_HEADTEACHER.join(', ')}\n` +
      `• Email: ${EMAIL_INFO}`,
    links: [L.contactPage, L.callMain, L.applyOnline],
    related: ['admissions-apply', 'fees', 'contact'],
  },

  // ======================================================================== Fees
  // Source: FeeStructures.vue (fee structures are PDF documents; no amounts appear in the page text)
  {
    id: 'fees',
    topic: 'fees',
    category: 'School fees',
    chip: 'School fees',
    questions: [
      'What are the school fees?',
      'How much is tuition?',
      'Where can I see the fee structure?',
      'How much do I pay per term?',
      'Fees for boarding',
    ],
    keywords: ['fee', 'fee structure', 'tuition', 'cost', 'price', 'charge', 'expensive', 'per term', 'how much', 'admission fee', 'functional fee', 'boarding fee', 'dayscholar fee'],
    answer:
      "I couldn't load the fee amounts just now. The fee structures are on the Fees, Rules & Calendar page (PDF), or you can ask the school office.",
    links: [L.feesPage, L.contactPage, L.callMain],
    related: ['fees-payment', 'calendar', 'admissions-apply'],
  },
  {
    id: 'fees-payment',
    topic: 'fees',
    category: 'School fees',
    chip: 'Paying fees',
    questions: [
      'How do I pay school fees?',
      'What is the school bank account?',
      'Can I pay in instalments?',
      'Do you accept mobile money?',
      'I have a fees balance',
    ],
    keywords: ['payment', 'bank', 'account number', 'momo', 'installment', 'arrears', 'balance', 'receipt', 'bursar', 'pay fees', 'scholarship', 'bursary', 'discount', 'sponsorship'],
    answer:
      "Payment methods, bank details, instalments, bursaries and scholarships aren't published. Please ask the school office.",
    links: [L.contactPage, L.callMain, L.feesPage],
    related: ['fees', 'contact'],
  },

  // ======================================================================== Contact
  // Source: AppFooter.vue, TopBar.vue, Contact.vue
  {
    id: 'contact',
    topic: 'contact',
    category: 'Contact information',
    chip: 'Contact the school',
    questions: [
      'How can I contact the school?',
      'What is the school phone number?',
      'What is the school email address?',
      'Can I call or WhatsApp the school?',
      'How do I get in touch with the school?',
    ],
    keywords: ['contact', 'phone number', 'email', 'telephone', 'call', 'whatsapp', 'reach', 'postal', 'p o box', 'po box', 'enquiry', 'inquiry', 'message the school'],
    weak: ['number', 'office'],
    exclude: ['login', 'password'],
    answer:
      `You can reach ${SCHOOL_NAME} on:\n\n` +
      `• Headteacher's Office: ${PHONE_HEADTEACHER.join(' / ')}\n` +
      `• Public Relations Officer (P.R.O): ${PHONE_PRO}\n` +
      `• Email: ${EMAIL_INFO} or ${EMAIL_DIRECTOR} (Director's Office)\n` +
      '• WhatsApp: +256 775 831 844\n' +
      `• Post: ${POSTAL}\n\n` +
      "You can also send a message through the Contact page. I'm an automated assistant, so messages typed here are not passed on to staff.",
    links: [L.contactPage, L.callMain, L.emailInfo, L.whatsapp],
    related: ['office-hours', 'parents-offices', 'location'],
  },
  {
    id: 'parents-offices',
    topic: 'contact',
    category: 'Contact information',
    chip: 'Which office to contact',
    questions: [
      'Which office should I contact?',
      'I am a parent, who should I talk to?',
      'How can parents contact the school?',
      'Contact the head teacher\'s office',
      'Contact the public relations officer',
    ],
    keywords: ['which office', 'parent', 'guardian', 'pro', 'public relations', 'headteacher office', 'director office'],
    weak: ['office', 'contact'],
    answer:
      'The school lists these offices:\n\n' +
      `• Headteacher's Office - ${PHONE_HEADTEACHER.join(' / ')}\n` +
      `• Public Relations Officer (P.R.O) - ${PHONE_PRO}\n` +
      `• Director's Office - ${EMAIL_DIRECTOR}\n` +
      `• General enquiries - ${EMAIL_INFO}\n\n` +
      "The website doesn't say which matters each office handles, so if you're unsure, call the Headteacher's Office or send a message through the Contact page and the school will direct it.",
    links: [L.contactPage, L.callMain, L.emailDirector],
    related: ['office-hours', 'contact'],
  },
  {
    id: 'office-hours',
    topic: 'contact',
    category: 'Contact information',
    chip: 'Office hours',
    questions: ['What are the office hours?', 'What time does the office open?', 'Is the office open on Saturday?', 'Is the office closed on Sunday?'],
    keywords: ['office hours', 'working hours', 'opening hours', 'office open', 'saturday', 'sunday', 'weekend', 'closing time'],
    weak: ['office', 'open', 'hours'],
    answer: `Office hours:\n• ${OFFICE_HOURS}`,
    links: [L.contactPage, L.callMain],
    related: ['contact', 'location'],
  },
  {
    id: 'speak-to-staff',
    topic: 'contact',
    category: 'Contact information',
    chip: 'Speak to a person',
    questions: ['I want to speak to a human', 'Can I talk to someone?', 'This is urgent', 'I have an emergency', 'I want to make a complaint'],
    keywords: ['human', 'real person', 'agent', 'someone', 'representative', 'urgent', 'emergency', 'complaint', 'complain', 'accident', 'speak to a person', 'talk to a person', 'staff member'],
    answer:
      "I'm an automated assistant, so I can't pass messages to staff or handle urgent matters. Please phone the school office directly:\n\n" +
      `• Headteacher's Office: ${PHONE_HEADTEACHER.join(' / ')}\n` +
      `• P.R.O: ${PHONE_PRO}\n\n` +
      `Office hours:\n• ${OFFICE_HOURS}`,
    links: [L.callMain, L.contactPage, L.whatsapp],
    related: ['contact', 'office-hours'],
  },
  {
    id: 'social-media',
    topic: 'contact',
    category: 'Contact information',
    chip: 'Social media',
    questions: ['Is the school on Facebook?', 'What are your social media pages?', 'Do you have a YouTube channel?'],
    keywords: ['facebook', 'twitter', 'youtube', 'tiktok', 'instagram', 'social media', 'follow'],
    answer: 'You can follow the school on social media:',
    links: [
      { label: 'Facebook', url: 'https://www.facebook.com/people/St-Marks-College-Namagoma/100057643021727/' },
      { label: 'X (Twitter)', url: 'https://x.com/Smacon24' },
      { label: 'YouTube', url: 'https://www.youtube.com/@stmarkscollegenamagoma6864' },
      { label: 'TikTok', url: 'https://www.tiktok.com/@smacon23' },
    ],
    related: ['news-events', 'contact'],
  },

  // ======================================================================== Location
  // Source: AppFooter.vue ("How to reach us", map pin)
  {
    id: 'location',
    topic: 'location',
    category: 'Location and directions',
    chip: 'Location & directions',
    questions: [
      'Where is the school located?',
      'How do I get to the school?',
      'What is the school address?',
      'Give me directions to the school',
      'How far is the school from Kampala?',
    ],
    keywords: ['location', 'direction', 'address', 'distance', 'map', 'masaka road', 'nabbingo', 'how to get there', 'find the school', 'situated', 'google maps'],
    weak: ['where'],
    answer:
      `${SCHOOL_NAME} is at Namagoma, along the Kampala-Masaka Road.\n\n` +
      '• By own transport: from Kampala drive about 16 km on Masaka Road (roughly 20-30 minutes). After Nabbingo Trading Center, look for the College signpost on the right, about 1 km from the main road.\n' +
      `• Postal address: ${POSTAL}\n\n` +
      'The links below open the exact campus pin and driving directions on Google Maps.',
    links: [
      { label: 'View on Google Maps', url: MAPS_PIN },
      { label: 'Get directions', url: MAPS_DIRECTIONS },
      L.contactPage,
    ],
    related: ['office-hours', 'contact', 'boarding'],
  },

  // ======================================================================== Academics
  // Source: academics/Index.vue
  {
    id: 'academics-overview',
    topic: 'academics',
    category: 'Academic programmes',
    chip: 'Academic programmes',
    questions: [
      'What academic programmes do you offer?',
      'Tell me about academics',
      'Do you offer Arts and Sciences?',
      'What levels do you teach?',
      'Do you teach lower and upper secondary?',
    ],
    keywords: ['academic', 'academic programme', 'academics', 'programme', 'levels', 'lower secondary', 'upper secondary', 'arts and sciences', 'sciences', 'education offered'],
    exclude: ['empowerment', 'portal'],
    answer:
      'We offer both Arts and Sciences at O-Level and A-Level.\n\n' +
      '• Lower Secondary: a strong academic foundation with emphasis on discipline, character formation and academic excellence.\n' +
      '• Upper Secondary: advanced learning programmes preparing students for higher education and professional careers.\n' +
      '• Co-Curricular Activities: sports, debates, clubs and leadership programmes for holistic development.',
    links: [L.academics, L.curriculum],
    related: ['subjects', 'uneb-results', 'calendar'],
    next: { prompt: 'Would you like to see the subjects offered?', entryId: 'subjects' },
  },
  // Source: academics/Curriculum.vue
  {
    id: 'subjects',
    topic: 'academics',
    category: 'Academic programmes',
    chip: 'Subjects offered',
    questions: [
      'What subjects do you offer?',
      'What are the A-Level subject combinations?',
      'What subjects are taught at O-Level?',
      'Which curriculum do you follow?',
      'Do you teach Physics and Chemistry?',
    ],
    keywords: ['subject', 'curriculum', 'combination', 'olevel', 'alevel', 'syllabus', 'competency', 'physics', 'chemistry', 'biology', 'mathematics', 'geography', 'history', 'ict', 'entrepreneurship', 'literature', 'kiswahili', 'luganda', 'economics', 'fine art', 'computer', 'computing'],
    weak: ['teach', 'offer'],
    answer:
      'The curriculum is delivered under the Competency-Based Curriculum.\n\n' +
      'O-Level\n' +
      '• Sciences: Mathematics, Chemistry, Biology, Physics\n' +
      '• Vocational: Entrepreneurship, ICT, Art and Design, Technical Drawing, Physical Education\n' +
      '• Languages: Kiswahili, Luganda, Literature, English\n' +
      '• Humanities: Religious Education, Geography, History, Islamic Religious Education (IRE)\n\n' +
      'A-Level (learners choose their preferred combination)\n' +
      'Physics, Chemistry, Biology, Mathematics, Geography, History, CRE / Divinity, Technical Drawing, Kiswahili, Luganda, Entrepreneurship, Literature, Fine Art, Economics',
    links: [L.curriculum, L.academics],
    related: ['academics-overview', 'uneb-results', 'co-curricular'],
  },
  // Source: academics/UnebResults.vue, HighAchievers (nav), Performances.vue
  {
    id: 'uneb-results',
    topic: 'academics',
    category: 'Academic programmes',
    chip: 'UNEB results',
    questions: [
      'What are your UNEB results?',
      'How does the school perform in national exams?',
      'Where can I see the exam results?',
      'Who are the high achievers?',
      'Where are the school circulars?',
    ],
    keywords: ['uneb', 'result', 'exam', 'performance', 'high achiever', 'circular', 'ranking', 'passed', 'pass rate', 'national examinations', 'report card', 'grades'],
    answer:
      'The school reports consistently strong results in the Uganda National Examinations Board (UNEB) assessments. I do not quote figures, because official results and detailed breakdowns come from the school administration.\n\n' +
      'You can see individual student recognitions on the High Achievers page, and published performance documents and circulars on the Performance page.',
    links: [
      { label: 'UNEB results', url: '/academics/uneb-results' },
      { label: 'High achievers', url: '/academics/high-achievers' },
      { label: 'Performance & circulars', url: '/performance' },
      L.contactPage,
    ],
    related: ['subjects', 'calendar'],
  },
  // Source: academics/SchoolCalendar.vue, FeeStructures.vue
  {
    id: 'calendar',
    topic: 'academics',
    category: 'School calendar',
    chip: 'School calendar',
    questions: [
      'What are the term dates?',
      'When does the next term start?',
      'Where can I see the school calendar?',
      'When are the school holidays?',
      'When is the opening day?',
    ],
    keywords: ['calendar', 'term', 'holiday', 'semester', 'opening day', 'closing day', 'reopen', 'next term', 'when does', 'key dates', 'academic year', 'timetable', 'report back', 'reporting day', 'reopening'],
    weak: ['when', 'date'],
    answer:
      'The School Calendar page lists key dates for the academic year, and the Fees, Rules & Calendar page has the published term calendars.\n\n' +
      'The school also shares term dates, holidays and key academic events with parents and guardians at the start of each term. I do not quote dates myself, because they can change - please check those pages or confirm with the office.',
    links: [L.calendar, L.feesPage, L.contactPage],
    related: ['fees', 'news-events', 'contact'],
  },

  // ======================================================================== Activities
  // Source: academics/CoCurricular.vue, academics/Index.vue
  {
    id: 'co-curricular',
    topic: 'activities',
    category: 'School activities',
    chip: 'School activities',
    questions: [
      'What school activities do you have?',
      'Do you offer sports?',
      'What co-curricular activities are there?',
      'Do students do music, dance and drama?',
      'What happens beyond the classroom?',
    ],
    keywords: ['activity', 'cocurricular', 'sport', 'music', 'dance', 'drama', 'talent', 'debate', 'extracurricular', 'beyond the classroom', 'competition'],
    answer:
      'Co-curricular life covers sports, music, dance, drama and talent - plus debates, clubs and leadership programmes - so learners grow beyond the classroom.\n\n' +
      'The Co-Curricular page shows the activities the school has published.',
    links: [{ label: 'Co-curricular activities', url: '/academics/co-curricular' }, { label: 'Clubs', url: '/clubs' }, { label: 'Gallery', url: '/explore/gallery' }],
    related: ['clubs', 'empowerment', 'news-events'],
  },
  // Source: nav (About -> Clubs), clubs/Index.vue is data-driven
  {
    id: 'clubs',
    topic: 'activities',
    category: 'School activities',
    chip: 'Clubs',
    questions: ['What clubs does the school have?', 'Can students join clubs?', 'Where can I see the list of clubs?'],
    keywords: ['club', 'societies', 'student leadership', 'prefect', 'student council', 'guild'],
    answer: 'The Clubs page lists the school clubs. Student leadership has its own page too.',
    links: [{ label: 'Clubs', url: '/clubs' }, { label: 'Student leadership', url: '/explore/student-leadership' }],
    related: ['co-curricular', 'empowerment'],
  },
  // Source: EmpowermentProgrammes.vue and its sub-pages (names only)
  {
    id: 'empowerment',
    topic: 'activities',
    category: 'School activities',
    chip: 'Empowerment programmes',
    questions: [
      'What are the empowerment programmes?',
      'Tell me about Girl-Boy Talk',
      'What is Inspiration Night?',
      'Do you have a mentorship programme?',
      'What is the Christmas Cantata?',
    ],
    keywords: ['empowerment', 'mentorship', 'mentor', 'girl boy talk', 'inspiration night', 'cantata', 'christmas', 'vocational', 'community project', 'leadership club'],
    answer:
      'Student empowerment programmes include leadership clubs, vocational workshops and community projects designed to prepare students for life beyond school.\n\n' +
      '• Leadership clubs: student councils, debate clubs and mentorship\n' +
      '• Vocational workshops: entrepreneurship, technology exposure and hands-on learning\n' +
      '• Community projects: outreach and service learning\n\n' +
      'Individual programme pages are linked below.',
    links: [
      { label: 'Empowerment programmes', url: '/empowerment-programmes' },
      { label: 'Mentorship', url: '/empowerment/mentorship' },
      { label: 'Girl-Boy Talk', url: '/empowerment/girl-boy-talk' },
      { label: 'Inspiration Night', url: '/empowerment/inspiration-night' },
      { label: 'Christmas Cantata', url: '/empowerment/christmas-cantata' },
    ],
    related: ['chaplaincy', 'alumni', 'clubs'],
  },
  // Source: nav (Empowerment -> SMOSA Alumni, SMOSA Feedback)
  {
    id: 'alumni',
    topic: 'activities',
    category: 'School activities',
    chip: 'SMOSA alumni',
    questions: ['Tell me about the alumni association', 'What is SMOSA?', 'I am an old student, how do I connect?'],
    keywords: ['smosa', 'alumni', 'old student', 'old boy', 'old girl', 'past student', 'former student', 'graduate'],
    answer:
      "The SMOSA Alumni page spotlights proud products of St. Mark's College Namagoma and where they have gone since. There is also a SMOSA feedback form for sharing your experience of SMOSA events.",
    links: [{ label: 'SMOSA alumni', url: '/empowerment/smosa-alumni' }, { label: 'SMOSA feedback', url: '/smosa-feedback' }],
    related: ['empowerment', 'news-events'],
  },

  // ======================================================================== Student life & support
  // Source: Uniform.vue
  {
    id: 'uniform',
    topic: 'life',
    category: 'Student life',
    chip: 'School uniform',
    questions: ['What is the school uniform?', 'What do O-Level students wear?', 'Which uniform do A-Level students wear?', 'What is in the uniform package?', 'Where can I buy the uniform?'],
    keywords: ['uniform', 'dress code', 'dressing', 'sweater', 'sportswear', 'what to wear', 'skirt', 'trousers', 'shirt'],
    answer:
      'Learners must be in full, correct uniform at all times during the school day.\n\n' +
      '• O-Level: cream shirts/blouses with navy blue trousers/skirts\n' +
      '• A-Level: blue shirts/blouses with navy blue trousers/skirts\n' +
      '• Sweater: navy blue with light blue stripes at the neck and sleeves\n' +
      '• Out of class: plain red T-shirts (different colours per class), black skirts/trousers\n\n' +
      'The uniform package (school sweater, 2 shirts/blouses, 2 trousers/skirts, Sunday wear, sports wear and 3 out-of-class T-shirts) is available for purchase directly at school. No other form of dressing is allowed.',
    links: [{ label: 'Uniform', url: '/explore/uniform' }],
    related: ['personal-needs', 'rules'],
  },
  // Source: PersonalNeeds.vue
  {
    id: 'personal-needs',
    topic: 'life',
    category: 'Student life',
    chip: 'Personal needs',
    questions: ['What are the personal needs for students?', 'What should my child bring to school?', 'Where is the requirements list for the term?'],
    keywords: ['personal needs', 'personal requirement', 'bring', 'toiletries', 'personal effects', 'packing list', 'what to carry', 'basic needs', 'dayscholar bring', 'boarding bring'],
    answer:
      'Students are expected to bring basic personal effects and toiletries for the term. A detailed personal needs list is given to parents and guardians at the start of each term. Published lists are on the Fees, Rules & Calendar page; for the current one, contact the school office.',
    links: [{ label: 'Personal needs', url: '/explore/personal-needs' }, L.feesPage, L.contactPage],
    related: ['uniform', 'calendar'],
  },
  // Source: FeeStructures.vue (rule books are published there), Home.vue (discipline)
  {
    id: 'rules',
    topic: 'life',
    category: 'Student life',
    chip: 'School rules',
    questions: ['What are the school rules?', 'Where can I read the rule book?', 'What is the discipline policy?', 'What are the school policies?'],
    keywords: ['rule', 'rules', 'rule book', 'policy', 'policies', 'discipline', 'regulations', 'code of conduct', 'behaviour', 'punishment'],
    answer:
      'The school rule book is published, together with fee structures, personal needs lists and term calendars, on the Fees, Rules & Calendar page. I cannot summarise it reliably, so please read the document itself.\n\n' +
      'For any policy question not answered there, please ask the school office.',
    links: [L.feesPage, L.contactPage],
    related: ['uniform', 'personal-needs'],
  },
  // Source: nav (Empowerment -> Chaplaincy), Home.vue (core values)
  {
    id: 'chaplaincy',
    topic: 'support',
    category: 'Student support',
    chip: 'Chaplaincy & spiritual life',
    questions: ['Do you have a chaplaincy?', 'Is there spiritual guidance for students?', 'What is the religious education like?'],
    keywords: ['chaplain', 'chaplaincy', 'spiritual', 'religion', 'religious', 'prayer', 'church', 'worship', 'godliness', 'christian', 'muslim', 'faith'],
    answer:
      'Godliness is the first of our core values, and the school has a Chaplaincy programme under its student empowerment activities. Religious Education and Islamic Religious Education (IRE) are also taught at O-Level.\n\n' +
      'The Chaplaincy page has more on the programme.',
    links: [{ label: 'Chaplaincy', url: '/empowerment/chaplaincy' }],
    related: ['motto-values', 'mentorship-support'],
  },
  // Source: Home.vue ("Why choose St. Mark's"), nav
  {
    id: 'mentorship-support',
    topic: 'support',
    category: 'Student support',
    chip: 'Student support',
    questions: ['What support do students get?', 'Is there guidance and counselling?', 'How do you look after students?', 'Is the class size small?'],
    keywords: ['support', 'pastoral', 'welfare', 'guidance', 'counselling', 'counseling', 'care', 'class size', 'attention', 'wellbeing', 'safety', 'safe'],
    answer:
      'According to the website, the school focuses on each student academically, spiritually and morally. Small class sizes and attentive mentorship are meant to ensure no learner is left behind, and the school also runs a Mentorship Programme and Chaplaincy.\n\n' +
      "The website doesn't describe counselling services in detail - please ask the school office for specifics.",
    links: [{ label: 'Mentorship programme', url: '/empowerment/mentorship' }, { label: 'Chaplaincy', url: '/empowerment/chaplaincy' }, L.contactPage],
    related: ['chaplaincy', 'empowerment', 'cybermonitor'],
  },
  // Source: Home.vue (Why choose), Welcome section, Gallery link
  {
    id: 'facilities',
    topic: 'support',
    category: 'Student support',
    chip: 'Facilities',
    questions: ['What facilities does the school have?', 'Do you have a library or laboratories?', 'What does the campus look like?', 'Are there sports grounds?'],
    keywords: ['facility', 'lab', 'library', 'campus', 'compound', 'playground', 'sports ground', 'classroom', 'infrastructure', 'environment', 'photos', 'pictures'],
    answer:
      'The website describes modern facilities and a serene learning environment on a 30-acre campus at Namagoma.\n\n' +
      "It doesn't list individual facilities (laboratories, library, sports grounds and so on), so I can't confirm those. The Gallery shows the campus and school life, and the office can answer specific questions.",
    links: [{ label: 'Gallery', url: '/explore/gallery' }, L.contactPage],
    related: ['boarding', 'location', 'co-curricular'],
  },

  // ======================================================================== News & events
  // Source: router (news, posts, campus-voices, gallery), nav
  {
    id: 'news-events',
    topic: 'news',
    category: 'News and events',
    chip: 'News & events',
    questions: ['What is the latest school news?', 'Are there any upcoming events?', 'Where can I see photos from school events?', 'What is eVoices?'],
    keywords: ['news', 'event', 'latest', 'update', 'announcement', 'gallery', 'campus voices', 'evoices', 'blog', 'happening', 'upcoming'],
    answer:
      'News, posts and student voices are published on the website, and the School Calendar lists upcoming events. Photos from school life are in the Gallery.',
    links: [{ label: 'News', url: '/news' }, { label: 'Posts', url: '/posts' }, { label: 'eVoices', url: '/campus-voices' }, L.calendar, { label: 'Gallery', url: '/explore/gallery' }],
    related: ['calendar', 'social-media', 'co-curricular'],
  },

  // ======================================================================== Careers
  // Source: explore/Career.vue, forms/Apply.vue
  {
    id: 'careers',
    topic: 'careers',
    category: 'Careers',
    chip: 'Jobs at the school',
    questions: ['Are there any job vacancies?', 'How do I apply for a teaching job?', 'I want to join your teaching staff', 'Where do I send my CV?'],
    keywords: ['job', 'vacancy', 'teaching job', 'work at', 'cv', 'employment', 'recruitment', 'career', 'hiring'],
    answer:
      'Teachers and support staff can apply through the Careers pages. The application form asks for your name, phone, email, address, the position you are applying for and a CV in PDF format (max 5MB).\n\n' +
      'If you need help applying, email the Director\'s Office or call the school.',
    links: [{ label: 'Careers', url: '/explore/career' }, { label: 'Application form', url: '/apply' }, L.emailDirector],
    related: ['contact', 'leadership'],
  },

  // ======================================================================== eSpace & digital campus
  // Source: Portal.vue, Home.vue (eCampus), DigitalCampus.vue
  {
    id: 'elearning-espace',
    topic: 'elearning',
    category: 'eSpace eLearning',
    chip: 'eSpace eLearning',
    questions: [
      'What is eSpace?',
      'How do I access eLearning?',
      'Where is the eLearning platform?',
      'Can students learn online?',
      'Where do I find class notes and assignments?',
    ],
    keywords: ['espace', 'elearning', 'online learning', 'virtual lesson', 'assignment', 'class notes', 'learning platform', 'online class', 'learn online', 'digital campus', 'ecampus'],
    exclude: ['password', 'login'],
    answer:
      'eSpace is the school\'s eLearning portal: class notes, assignments and virtual lessons, available through the link below.\n\n' +
      'I cannot see or change anyone\'s eSpace account - for account help, please contact the school.',
    links: [L.espace, { label: 'eLearning page', url: '/elearning' }, L.portal],
    related: ['espace-login-help', 'digital-systems', 'contact'],
  },
  {
    id: 'espace-login-help',
    topic: 'elearning',
    category: 'eSpace eLearning',
    chip: 'Login help',
    questions: ['I cannot log in to eSpace', 'I forgot my password', 'How do I reset my eSpace password?', 'My account does not work'],
    keywords: ['login', 'password', 'username', 'forgot', 'reset', 'locked', 'cannot access', 'account', 'sign in', 'log in', 'problem', 'trouble', 'error', 'not working'],
    answer:
      'I cannot see, reset or check any account, and you should never type a password into this chat.\n\n' +
      'To get help with eSpace sign-in, please contact the school office. When you call, have the student\'s name and class ready.',
    links: [L.espace, L.contactPage, L.callMain],
    related: ['elearning-espace', 'contact'],
  },
  {
    id: 'digital-systems',
    topic: 'elearning',
    category: 'eSpace eLearning',
    chip: 'School systems',
    questions: ['What digital systems does the school have?', 'Tell me about eVoting', 'What is the academics portal?', 'Where is the school systems portal?'],
    keywords: ['evoting', 'cybermonitor', 'econcerting', 'portal', 'digital', 'system', 'concert', 'voting', 'election', 'online safety'],
    answer:
      'The School Systems Portal links to every digital system built for the college:\n\n' +
      '• Academics - student records, timetables & scheduling, performance tracking\n' +
      '• Admissions - apply and track an application online\n' +
      '• Cyber Monitor - the laptop management system\n' +
      '• eConcerting - livestreamed and recorded concerts and events\n' +
      '• eLearning (eSpace) - notes, assignments and virtual lessons\n' +
      '• eVoting - secure digital voting for student elections',
    links: [L.portal, L.espace, L.applyOnline],
    related: ['elearning-espace', 'admissions-apply'],
  },
  {
    id: 'cybermonitor',
    topic: 'elearning',
    category: 'eSpace eLearning',
    chip: 'Cyber Monitor',
    questions: ['What is Cyber Monitor?', 'How does the school manage student laptops?', 'What is the laptop management system?'],
    keywords: ['cybermonitor', 'cyber', 'laptop management', 'manage laptop', 'online safety', 'internet safety', 'cyberbullying'],
    answer: "Cyber Monitor is the school's laptop management system.",
    links: [{ label: 'Cyber Monitor', url: 'https://cybermonitor.stmark.sc.ug/' }, L.portal],
    related: ['digital-systems', 'mentorship-support'],
  },

  // ======================================================================== Not published / meta
  {
    id: 'not-published',
    topic: 'meta',
    category: 'Not published',
    chip: 'Other questions',
    questions: [
      'Do you provide school transport?',
      'What meals do students get?',
      'Is there a school clinic or nurse?',
      'When are visitation days?',
    ],
    keywords: ['transport', 'bus', 'food', 'meal', 'diet', 'lunch', 'medical', 'clinic', 'nurse', 'sickbay', 'visitation', 'visiting day', 'exeat', 'pocket money', 'tuck shop', 'canteen', 'wifi'],
    answer:
      "That information isn't available. The school office can tell you:",
    links: CONTACT_LINKS,
    related: ['contact', 'office-hours', 'faq'],
  },
  {
    id: 'faq',
    topic: 'meta',
    category: 'Frequently asked questions',
    chip: 'FAQs',
    questions: ['Frequently asked questions', 'FAQ', 'What are the common questions?'],
    keywords: ['faq', 'frequently asked question', 'common question', 'popular question'],
    answer: 'Here are the questions visitors ask most. Tap one, or type your own:',
    related: ['admissions-apply', 'admissions-requirements', 'fees', 'contact', 'location', 'subjects', 'calendar', 'uniform', 'elearning-espace', 'co-curricular'],
  },
]

/** Buttons shown when the chat first opens. `query` is what is sent when tapped. */
export const QUICK_REPLIES: Array<{ label: string; query: string }> = [
  { label: 'Admissions', query: 'How do I apply for admission?' },
  { label: 'School fees', query: 'What are the school fees?' },
  { label: 'Contact the school', query: 'How can I contact the school?' },
  { label: 'Academic programmes', query: 'What academic programmes do you offer?' },
  { label: 'School location', query: 'Where is the school located?' },
  { label: 'School activities', query: 'What school activities do you have?' },
  { label: 'eSpace eLearning', query: 'What is eSpace?' },
  { label: 'Frequently asked questions', query: 'Frequently asked questions' },
]
