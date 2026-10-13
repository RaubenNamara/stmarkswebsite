/**
 * Text normalisation for the chatbot. Both the visitor's question and every knowledge-base
 * keyword/example question go through exactly the same pipeline, so they always meet in the same
 * "canonical token" space:
 *
 *   lower-case -> strip accents/punctuation -> fix common misspellings -> collapse multi-word
 *   phrases ("how much" -> "fee") -> light stemming ("fees" -> "fee") -> synonym folding
 *   ("tuition" -> "fee") -> drop filler words.
 *
 * Maintenance: to teach the bot a new synonym or misspelling, add it to SYNONYMS / MISSPELLINGS /
 * PHRASES below (synonym keys are matched against the raw word AND its stem). Then run
 * `npm test` - the tests will tell you if something else changed behaviour.
 */

/** Multi-word phrases collapsed to a single canonical word before tokenising. Order matters. */
const PHRASES: Array<[RegExp, string | ((...match: string[]) => string)]> = [
  [/\bjoin (the |your |our )?(\w+ )?(team|staff)\b/g, 'job'],
  [/\b(is|are) (the |your )?(school|college) (registered|licensed|accredited|recognised|recognized)\b/g, 'accredited'],
  [/\bregistered (with|by)\b/g, 'accredited'],
  [/\b(tell me )?about (the |your |this )?(school|college|st marks( college)?( namagoma)?|namagoma|smacon)\b/g, 'overview'],
  [/\bhow many (students|pupils|learners)\b/g, 'studentcount'],
  [/\bnumber of (students|pupils|learners)\b/g, 'studentcount'],
  [/\bwhat is (st marks|the school|this school|your school)( college)?( namagoma)?$/g, 'overview'],
  [/\bwhy (is|are|was) .{0,30}\bcalled\b/g, 'collegename'],
  [/\b(meaning|origin) of the (college )?name\b/g, 'collegename'],
  [/\bwho (own|owns|owned)\b/g, 'owner'],
  [/\bwork (at|for|with)\b/g, 'job'],
  [/\bhow much\b/g, 'fee'],
  [/\bget in touch\b/g, 'contact'],
  [/\b(speak|talk|chat) (to|with)\b/g, 'contact'],
  [/\bstudy (at|in)\b/g, 'admission'],
  [/\bsend (my|our|a|the) (child|son|daughter|kid)\b/g, 'admission'],
  [/\bsign ?up\b/g, 'admission'],
  [/\bhow (do i|can i|to|do we|can we) get (to|there|here)\b/g, 'direction'],
  [/\bhow far\b/g, 'distance'],
  // Location phrases only fire when the school itself is the object (end of question, or "from ..."),
  // so "where is the school anthem" / "where can I see the school calendar" are not read as location.
  [/\bwhere (is|are) (the |your )?(school|college|campus)(?= ?$|\s+(from|located|situated|in|at)\b)/g, 'location'],
  [/\bwhere (can|do|could) (i|we) (find|locate|see) (the |your )?(school|college|campus)(?= ?$|\s+(from|located|situated|in|at)\b)/g, 'location'],
  [/\b(find|reach|visit|come to|go to|get to) (the |your |you |st marks )?(school|college|campus|namagoma)(?= ?$|\s+(from|by|please|today|tomorrow)\b)/g, 'direction'],
  [/\be learning\b/g, 'elearning'],
  [/\be space\b/g, 'espace'],
  [/\be voting\b/g, 'evoting'],
  [/\be concerting\b/g, 'econcerting'],
  [/\bcyber monitor\b/g, 'cybermonitor'],
  [/\b(o|ordinary) level\b/g, 'olevel'],
  [/\b(a|advanced) level\b/g, 'alevel'],
  [/\bhead ?(teacher|master|mistress)\b/g, 'headteacher'],
  [/\bco ?curricular\b/g, 'cocurricular'],
  [/\bchristian religious education\b/g, 'cre'],
  [/\bislamic religious education\b/g, 'ire'],
  [/\b(p e|pe|phys ed|physical ed|sports? education)\b/g, 'physical education'],
  [/\b(gp|general paper)\b/g, 'general paper'],
  [/\b(fine art|art and design)\b/g, 'art'],
  // "IT" as a department/subject name (not the pronoun): "IT teachers", "teachers in IT", "who teaches IT"
  [/\bit (?=teacher|staff|department|dept|head|tutor|lab)/g, 'ict '],
  [/\b(teachers?|staff|teach|teaches|head|heads|hod) (in|for|of|at) it\b/g, '$1 $2 ict'],
  [/\bteach(?:es)? it\b/g, 'teaches ict'],
  [/\bterm (one|i|1)\b/g, 'term1'],
  [/\bterm (two|ii|2)\b/g, 'term2'],
  [/\bterm (three|iii|3)\b/g, 'term3'],
  [/\b(senior|form) (one|two|three|four|five|six)\b/g, (_m, _k, n) => 's' + ({ one: 1, two: 2, three: 3, four: 4, five: 5, six: 6 } as Record<string, number>)[n]],
  [/\b(senior|s|form) ?([1-6])\b/g, 's$2'],
  [/\bterm dates?\b/g, 'calendar'],
  [/\bopening date\b/g, 'calendar'],
  [/\bmobile money\b/g, 'momo'],
  [/\bday scholars?\b/g, 'dayscholar'],
  [/\bpass ?mark\b/g, 'cutoff'],
  [/\bcut ?off\b/g, 'cutoff'],
]

export const MISSPELLINGS: Record<string, string> = {
  admision: 'admission', admisson: 'admission', addmission: 'admission', admisions: 'admission', admition: 'admission',
  scool: 'school', skool: 'school', shool: 'school', sch: 'school',
  tution: 'tuition', tuishon: 'tuition', fees: 'fee', feez: 'fee',
  adress: 'address', addres: 'address', adres: 'address',
  contct: 'contact', contac: 'contact', kontakt: 'contact', phon: 'phone', fone: 'phone',
  calender: 'calendar', calandar: 'calendar', calander: 'calendar',
  sylabus: 'syllabus', syllabi: 'syllabus', curiculum: 'curriculum', curriculam: 'curriculum', curriculm: 'curriculum',
  unifrom: 'uniform', uniforn: 'uniform', unifom: 'uniform',
  subjet: 'subject', subjct: 'subject', subjets: 'subject',
  accademic: 'academic', academc: 'academic', acadamic: 'academic', academik: 'academic',
  loaction: 'location', locaton: 'location', direcion: 'direction', directon: 'direction',
  lerning: 'learning', elerning: 'elearning', elearn: 'elearning',
  requirments: 'requirements', requirment: 'requirement', requirements: 'requirement',
  boardin: 'boarding', bording: 'boarding', borading: 'boarding',
  telefone: 'telephone', telphone: 'telephone', whatsap: 'whatsapp', watsapp: 'whatsapp', emial: 'email', emal: 'email',
  gud: 'good', gd: 'good', gudbye: 'goodbye', gudnite: 'goodnight', mornin: 'morning', evenin: 'evening', afternoo: 'afternoon',
  helo: 'hello', hllo: 'hello', hy: 'hi', thanx: 'thanks', thanku: 'thanks', thnk: 'thank', thnks: 'thanks',
  shud: 'should', wud: 'would', cud: 'could', dnt: 'dont', cn: 'can', wt: 'what', hu: 'who', wch: 'which', brng: 'bring',
  tuishun: 'tuition', tuision: 'tuition', tuishen: 'tuition',
  fes: 'fee', fess: 'fee', feees: 'fee', pyment: 'payment', paymnt: 'payment',
  vistation: 'visitation', vastation: 'visitation', visitaton: 'visitation',
  plz: 'please', pls: 'please', thx: 'thanks', thnx: 'thanks', tnx: 'thanks', ty: 'thanks',
  abt: 'about', wat: 'what', wht: 'what', hw: 'how', wen: 'when', wer: 'where', whr: 'where',
  u: 'you', ur: 'your', r: 'are', hlp: 'help', info: 'information', infor: 'information',
}

/** Folds equivalent words onto one canonical concept word. Keys are raw words or stems. */
export const SYNONYMS: Record<string, string> = {
  // admissions
  admission: 'admission', admit: 'admission', admitt: 'admission', apply: 'admission', appli: 'admission',
  application: 'admission', applicant: 'admission', enrol: 'admission', enroll: 'admission',
  enrolment: 'admission', enrollment: 'admission', register: 'admission', registration: 'admission',
  join: 'admission', intake: 'admission', recruit: 'job', newcomer: 'admission',
  require: 'requirement', requir: 'requirement', requirement: 'requirement',
  // fees
  fee: 'fee', tuition: 'fee', cost: 'fee', price: 'fee', charge: 'fee', expensive: 'fee', cheap: 'fee',
  afford: 'fee', pay: 'payment', payment: 'payment', instalment: 'payment', installment: 'payment',
  // contact
  contact: 'contact', phone: 'contact', telephone: 'contact', mobile: 'contact',
  email: 'contact', mail: 'contact', reach: 'contact', hotline: 'contact', whatsapp: 'contact',
  // location
  location: 'location', locat: 'location', direction: 'direction', address: 'location', map: 'location',
  distance: 'distance', route: 'direction', situated: 'location', situat: 'location',
  // academics
  academic: 'academic', subject: 'subject', course: 'subject', combination: 'subject', syllabus: 'curriculum',
  curriculum: 'curriculum', cbc: 'curriculum', lesson: 'lesson', exam: 'exam', uneb: 'uneb', ple: 'uneb',
  uce: 'uneb', uace: 'uneb', result: 'result', grade: 'result', performance: 'performance',
  program: 'programme', programme: 'programme',
  // activities
  sport: 'sport', game: 'sport', football: 'sport', netball: 'sport', athletics: 'sport', athletic: 'sport',
  music: 'music', choir: 'music', band: 'music', dance: 'dance', drama: 'drama', debate: 'debate',
  club: 'club', society: 'club', extracurricular: 'cocurricular', activity: 'activity', activiti: 'activity',
  // people / jobs
  owns: 'owner', owned: 'owner', owner: 'owner', ownership: 'owner', proprietor: 'owner', proprietorship: 'owner',
  vacancy: 'job', vacanci: 'job', job: 'job', career: 'job', employ: 'job', employment: 'job', hiring: 'job', hir: 'job', cv: 'job',
  // digital
  elearning: 'elearning', espace: 'espace', evoting: 'evoting', econcerting: 'econcerting', cybermonitor: 'cybermonitor',
  login: 'login', logon: 'login', username: 'login', password: 'password', passcode: 'password',
  // months and days: full names, abbreviations and "sept" all fold to one 3-letter form
  january: 'jan', february: 'feb', march: 'mar', april: 'apr', june: 'jun', july: 'jul', august: 'aug',
  sept: 'sep', september: 'sep', october: 'oct', november: 'nov', december: 'dec',
  monday: 'mon', tuesday: 'tue', wednesday: 'wed', thursday: 'thu', friday: 'fri', saturday: 'sat', sunday: 'sun',
  // subject abbreviations used in school documents
  // Each subject has ONE canonical token; its short names, full name, plural form and common
  // spellings all fold onto it (the full names are listed too: stemming would turn "mathematics" into
  // "mathematic" while "maths" maps to the un-stemmed value, so they must be keys themselves).
  bio: 'biology', biology: 'biology', biological: 'biology',
  chem: 'chemistry', chemistry: 'chemistry', chemical: 'chemistry',
  phy: 'physics', phys: 'physics', physic: 'physics', physics: 'physics',
  math: 'mathematics', maths: 'mathematics', mathematic: 'mathematics', mathematics: 'mathematics', mtc: 'mathematics',
  geog: 'geography', geo: 'geography', geography: 'geography',
  hist: 'history', history: 'history',
  eco: 'economics', econ: 'economics', econs: 'economics', economic: 'economics', economics: 'economics',
  ent: 'entrepreneurship', entre: 'entrepreneurship', entrepreneurship: 'entrepreneurship', entrepreneur: 'entrepreneurship',
  lit: 'literature', literature: 'literature',
  eng: 'english', english: 'english',
  lug: 'luganda', luganda: 'luganda',
  kisw: 'kiswahili', swahili: 'kiswahili', kiswahili: 'kiswahili',
  agric: 'agriculture', agri: 'agriculture', agriculture: 'agriculture',
  divinity: 'cre', cre: 'cre', ire: 'ire',
  computer: 'ict', computing: 'ict', ict: 'ict', informatics: 'ict',
  art: 'art', arts: 'art',

  boarder: 'boarding', start: 'begin',
  spoke: 'speaker', spoken: 'speaker', speak: 'speaker', speaker: 'speaker', speaking: 'speaker',
  chairman: 'chair', chairperson: 'chair', chair: 'chair',
  // misc
  principal: 'headteacher', headteacher: 'headteacher', governor: 'board', board: 'board',
  timetable: 'timetable', holiday: 'holiday', vacation: 'holiday',
  facility: 'facility', facilitie: 'facility', laboratory: 'lab', laboratori: 'lab', lab: 'lab',
  hostel: 'boarding', dormitory: 'boarding', dormitori: 'boarding', dorm: 'boarding', boarding: 'boarding',
  residential: 'boarding',
}

/** Words that carry no topic information. Kept small and deliberate. */
const STOPWORDS = new Set([
  'a', 'an', 'the', 'is', 'are', 'was', 'were', 'be', 'been', 'am', 'do', 'does', 'did', 'can', 'could',
  'would', 'will', 'shall', 'should', 'may', 'might', 'must', 'have', 'has', 'had', 'i', 'me', 'my', 'we',
  'our', 'us', 'you', 'your', 'he', 'she', 'it', 'its', 'they', 'them', 'their', 'to', 'of', 'in', 'on', 'at',
  'for', 'from', 'by', 'with', 'and', 'or', 'but', 'if', 'so', 'this', 'that', 'these', 'those', 'there',
  'here', 'what', 'whats', 'which', 'who', 'whom', 'how', 'why', 'any', 'some', 'about', 'please', 'tell',
  'know', 'want', 'wanna', 'need', 'like', 'give', 'get', 'got', 'also', 'just', 'ok', 'okay', 'hi',
  'hello', 'hey', 'help', 'information', 'detail', 'more', 'much', 'many', 'very', 'really', 'as', 'than',
  'then', 'now', 'out', 'up', 'into', 'over', 'child', 'children', 'kid', 'son', 'daughter', 'ask',
  'school', 'college', 'st', 'mark', 'marks', 'namagoma', 'smacon', 'smaco', 'one', 'am', 'im', 'ive',
  'called', 'mean', 'meant', 'imean', 'lot', 'lots', 'id', 'ill', 'dont', 'doesnt', 'cant', 'wont', 'thing', 'things', 'know', 'let', 'lets', 'make', 'take',
  'go', 'going', 'come', 'see', 'look', 'find', 'use', 'using',
])

/** True for filler words that carry no topic (used by the spelling corrector). */
export const isStopword = (w: string): boolean => STOPWORDS.has(w)
export const stopwordList = (): string[] => [...STOPWORDS]

/** Lower-case, strip accents, turn punctuation into spaces and collapse whitespace. */
export function normalize(input: string): string {
  return input
    .normalize('NFKD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/['’`]/g, '') // "mark's" -> "marks", "what's" -> "whats"
    .replace(/[^a-z0-9]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

function collapseDouble(w: string): string {
  return /([^aeioulsz])\1$/.test(w) ? w.slice(0, -1) : w
}

/** Very light suffix stripping - just enough for plurals and -ing/-ed forms. */
export function stem(word: string): string {
  let w = word
  if (w.length <= 3 || /\d/.test(w)) return w
  if (w.endsWith('ies') && w.length > 4) return w.slice(0, -3) + 'y'
  if (/(sses|xes|ches|shes)$/.test(w)) return w.slice(0, -2)
  if (w.endsWith('ing') && w.length > 5) return collapseDouble(w.slice(0, -3))
  if (w.endsWith('ed') && w.length > 4) return collapseDouble(w.slice(0, -2))
  if (w.endsWith('s') && !/(ss|us|is)$/.test(w)) w = w.slice(0, -1)
  return w
}

/** Raw word -> canonical token (misspelling fix, synonym folding or plain stem). */
export function canon(raw: string): string {
  const fixed = MISSPELLINGS[raw] ?? raw
  return SYNONYMS[fixed] ?? SYNONYMS[stem(fixed)] ?? stem(fixed)
}

/** Normalises text and returns the ordered list of canonical content tokens. */
export function tokens(input: string): string[] {
  let s = normalize(input)
  for (const [pattern, replacement] of PHRASES) s = s.replace(pattern, replacement as string)
  const out: string[] = []
  for (const raw of s.split(' ')) {
    if (!raw) continue
    const t = canon(raw)
    if (STOPWORDS.has(raw) || STOPWORDS.has(t)) continue
    out.push(t)
  }
  return out
}

/** True when the normalised text matches a whole word/phrase list entry at the start. */
export function startsWithPhrase(normalized: string, phrase: string): boolean {
  return normalized === phrase || normalized.startsWith(phrase + ' ')
}
