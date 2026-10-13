# School assistant (rule-based chatbot)

A free, rule-based assistant. **No AI model, no API key, no external service.** It answers from three
sources, in this order of precision:

1. **Live data** - the school's own database (calendar events, news, clubs, staff roles, programmes ...)
   and the **text of uploaded PDFs** (fee structures, term programme). Figures, dates and list items are
   quoted verbatim and attributed ("From 'Boarding fees structure 2026': ...") with a link to the document.
2. **Built-in answers** (`knowledge.ts`) - verified facts from the website's pages (contacts, admissions
   process, location, curriculum, uniform ...). Used when live data has nothing better, or is unavailable.
3. **Honest fallback** - when neither source covers the question the bot says so and points to the office.

## How live data works

```
uploaded PDF / DB rows
      |  PHP: shared/src/Services/ChatbotKnowledgeService.php  (+ PdfTextExtractor.php, smalot/pdfparser, cached in storage/cache/pdf-text)
      v
GET /api/public/chatbot-knowledge          (one JSON feed, public content only, cached 5 min)
      |  browser: live.ts  (fetched once, when the chat is first opened; validated; cached 10 min in sessionStorage)
      v
liveIndex.ts   split into passages, IDF-weighted search
liveAnswer.ts  precise resolvers: fees (parses the fee tables, O-level/A-level columns), personal-requirement
               lists, calendar events/upcoming/month listings, leadership, general passage retrieval
```

Precision rules (enforced by `tests/chatbot-live.test.ts`): amounts and dates are copied from the source,
never calculated or rephrased; every number/date in a reply must exist in the feed; a question with
words the school's content never uses is not forced onto a loosely related passage; if the feed can't be
loaded the bot silently keeps using the built-in answers.

**PDF requirements:** the PDF must have a text layer (Word/Excel/"Save as PDF" exports do). A scanned
image PDF has no text; the bot then only links to it. Replacing a PDF in the admin refreshes its text
automatically (the cache is keyed by file modification time).

**Results PDFs** (`performances`) list individual students' names and grades. By default only their
title/link is used and their text is never read out. To change that, set `INDEX_RESULTS_PDF_TEXT = true` in
`ChatbotKnowledgeService.php`.

## Files

| File | Role |
|---|---|
| `knowledge.ts` | Built-in FAQ/knowledge base (edit this to add or change built-in answers) |
| `live.ts` | Fetches + validates the live feed |
| `liveIndex.ts` | PDF/prose -> passages, search index |
| `liveAnswer.ts` | Precise live answers (fees, requirements, calendar, leadership, passages) |
| `text.ts` | Normalisation: case, punctuation, misspellings, synonyms, plurals, months, "S.4", "Term III" |
| `matcher.ts` | Scoring of built-in entries |
| `engine.ts` | Decision logic: small talk, privacy check, built-in vs live, clarification, fallback |
| `links.ts` | Allow-list validation for every link the bot can show |
| `storage.ts` | Optional localStorage history (7 days, last 60 messages) |
| `useChatbot.ts` | Vue state: messages, typing indicator, live loading |
| `../components/chatbot/` | `ChatbotWidget.vue` (launcher + window), `ChatMessage.vue` (bubble) |
| `../../tests/` | `chatbot.test.ts`, `chatbot-live.test.ts` (+ `fixtures/knowledge.json`, a real feed snapshot) |

## Add or update an answer

- **Dynamic content** (events, news, clubs, staff, PDFs): just edit it in the admin. The bot picks it up
  within minutes - no code change. To teach it a *new kind* of content, add it to
  `ChatbotKnowledgeService::sources()` and (if needed) a formatter in `liveAnswer.ts`.
- **Built-in facts:** edit `knowledge.ts` (instructions at the top of that file), then `npm test`.
- **A synonym or misspelling:** add it to `text.ts`.
- If a fact changes on the site (phone, email, hours), update the constants at the top of `knowledge.ts`.

## Privacy

- Nothing is sent to any third party. The only requests go to the school's own server: one GET for the public
  knowledge feed, and (see *Analytics* below) one anonymous POST per question for the admin's Chatbot Analytics.
- Chat history stays in the visitor's own `localStorage` (`smacon-chatbot:v1`); "Start a new chat"
  erases it. If storage is blocked, the chat simply doesn't remember.
- Messages that look like passwords, PINs, card numbers or long digit strings are not answered, are
  shown as hidden, and are never stored.
- The feed contains only content already public on the website (no contact-form messages, applications,
  users or analytics). It cannot see eSpace or any student record.

## Spelling tolerance (`spell.ts`)

Before matching, every word the bot doesn't recognise is replaced by the nearest word it does know
(Damerau-Levenshtein edit distance: insert / delete / replace / swapped letters; limits grow with word
length). Examples: `adimssion`, `scool fes`, `chemestry techers`, `wher is teh schol`, `gud mornin`,
`schoolfees`, `ad mission`.

- Corrections can only land on **core vocabulary** (built-in keywords/questions, small talk, subjects,
  staff/club names, titles, calendar entries, fee/PDF terms). Words that merely occur in article text
  only *protect* ordinary English from being "corrected" (so "weather" never becomes "whether").
- A word that resembles nothing (e.g. "bitcoin") is left alone, so the bot still says it doesn't know.
- One-letter substitutions on short words (cook/cool, write/white) are not corrected.
- Well-known slips and chat shorthand live in `MISSPELLINGS` in `text.ts` ("ur", "gud", "shud", "telefone").
  To teach it one more, add it there; `npm test` (see `tests/chatbot-spelling.test.ts`) guards against over-correction.


## Analytics (admin: *Chatbot Analytics*)

After each answer the browser sends one anonymous record to `POST /api/public/chatbot-log` (`analytics.ts`):
the question, how the bot answered (topic / live resolver / not answered), whether spelling was corrected, the
page, and the start of the reply. The admin page `/admin/chatbot` shows people, conversations, top questions,
**questions the bot could not answer** (the to-do list for improving it), topics, busiest hours, devices,
full transcripts, search, CSV export and delete.

- **Anonymous:** visitors are a random id made in their own browser. No name, email, cookie or raw IP is stored
  (`ip_hash` is salted and rotates monthly, only for spotting floods).
- **Redacted twice:** passwords/PINs/cards/long numbers are never sent (only `[private]`); the server also
  blanks emails and phone-like numbers before saving.
- **Respects "Do Not Track"**, is rate-limited (`rate_limit:chatbot_log,120,300`), and never affects the chat if it fails.
- **Disclosure:** the chat window does not currently tell visitors their questions are recorded. If you want to, say so in a privacy notice on the site (for example a link in the footer).
- **Retention:** conversations are deleted automatically after 365 days (`ChatbotAnalyticsService::RETENTION_DAYS`);
  admins can also delete one conversation or purge older ones.
- **Setup:** `cd shared && php scripts/migrate.php migrate` creates the two tables (`chatbot_conversations`, `chatbot_messages`).
