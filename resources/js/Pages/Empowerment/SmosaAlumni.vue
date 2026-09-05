<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  alumni: {
    type: Array,
    default: () => []
  }
})

function embedUrl(link) {
  if (!link) return ''

  if (link.includes('youtu.be/')) {
    const id = link.split('youtu.be/')[1].split(/[?&]/)[0]
    return `https://www.youtube.com/embed/${id}`
  }

  if (link.includes('youtube.com/watch')) {
    const url = new URL(link)
    const videoId = url.searchParams.get('v')
    return videoId ? `https://www.youtube.com/embed/${videoId}` : link
  }

  if (link.includes('/embed/')) {
    return link
  }

  return link
}
</script>

<template>
  <div class="page">
    <div class="page-shell">
      <!-- HEADER -->
      <header class="header">
        <div class="header-badge">SMOSA</div>
        <h1>SMOSA Alumni</h1>
        <p>St. Mark's Old Students Association</p>
      </header>

      <!-- INTRO -->
      <section class="intro-card">
        <p class="content">
          SMOSA (St. Mark's Old Students Association) connects former students of St. Mark's College Namagoma.
          The association strengthens the relationship between alumni and the school community by supporting
          academic activities, mentoring students and contributing to the development of the institution.
        </p>
      </section>

      <!-- GRID -->
      <section class="alumni-grid">
        <article
          v-for="a in alumni"
          :key="a.id"
          class="alumni-card"
        >
          <div class="card-top">
            <!-- PHOTO -->
            <div class="photo-wrap">
              <img
                v-if="a.photo_url"
                :src="a.photo_url"
                class="photo"
                alt="Alumni photo"
              />
              <div v-else class="photo placeholder">
                No Photo
              </div>
            </div>

            <div class="identity">
              <!-- NAME -->
              <h3 class="name">
                {{ a.name }}
              </h3>

              <!-- PROFESSION -->
              <p v-if="a.profession" class="profession">
                {{ a.profession }}
              </p>
            </div>
          </div>

          <!-- MESSAGE -->
          <p v-if="a.message" class="message">
            {{ a.message }}
          </p>

          <!-- VIDEO -->
          <div v-if="a.video" class="video">
            <iframe
              :src="embedUrl(a.video)"
              frameborder="0"
              allowfullscreen
              class="video-frame"
            ></iframe>
          </div>

          <div v-else-if="a.video_url" class="video">
            <iframe
              :src="a.video_url"
              frameborder="0"
              allowfullscreen
              class="video-frame"
            ></iframe>
          </div>
        </article>
      </section>
    </div>
  </div>
</template>

<style scoped>
/* PAGE */
.page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top, rgba(99, 102, 241, 0.10), transparent 30%),
    linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
  padding: 48px 16px 64px;
}

.page-shell {
  max-width: 1400px;
  margin: 0 auto;
}

@media (min-width: 1536px) {
  .page {
    padding: 64px 24px 80px;
  }

  .page-shell {
    max-width: 1700px;
  }
}

@media (min-width: 1920px) {
  .page-shell {
    max-width: 1900px;
  }
}

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 28px;
  padding: 28px 20px;
}

.header-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  margin-bottom: 14px;
  border-radius: 999px;
  background: rgba(55, 48, 163, 0.10);
  color: #3730a3;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.header h1 {
  font-size: clamp(2rem, 3vw, 3.25rem);
  line-height: 1.1;
  font-weight: 900;
  color: #1e1b4b;
  margin: 0;
}

.header p {
  color: #475569;
  font-size: clamp(1rem, 1.2vw, 1.15rem);
  margin-top: 10px;
}

/* INTRO CARD */
.intro-card {
  background: rgba(255, 255, 255, 0.86);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(224, 231, 255, 0.9);
  border-radius: 24px;
  padding: clamp(20px, 3vw, 36px);
  box-shadow:
    0 10px 30px rgba(15, 23, 42, 0.06),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
  margin-bottom: 32px;
}

.content {
  color: #334155;
  font-size: clamp(0.98rem, 1.05vw, 1.08rem);
  line-height: 1.9;
  text-align: justify;
  text-justify: inter-word;
  margin: 0;
}

/* GRID */
.alumni-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

@media (min-width: 640px) {
  .alumni-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
  }
}

/* Desktop: exactly two cards per row */
@media (min-width: 1024px) {
  .alumni-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 26px;
  }
}

@media (min-width: 1536px) {
  .alumni-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 30px;
  }
}

/* CARD */
.alumni-card {
  position: relative;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.98));
  border: 1px solid rgba(226, 232, 240, 0.95);
  border-radius: 24px;
  padding: clamp(18px, 2.3vw, 28px);
  box-shadow:
    0 12px 30px rgba(15, 23, 42, 0.08),
    0 2px 6px rgba(15, 23, 42, 0.04);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.alumni-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), transparent 45%);
  pointer-events: none;
}

.alumni-card:hover {
  transform: translateY(-4px);
  border-color: rgba(99, 102, 241, 0.25);
  box-shadow:
    0 18px 40px rgba(15, 23, 42, 0.12),
    0 6px 14px rgba(15, 23, 42, 0.06);
}

/* TOP SECTION */
.card-top {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 14px;
}

/* PHOTO */
.photo-wrap {
  flex-shrink: 0;
}

.photo {
  width: 112px;
  height: 112px;
  object-fit: cover;
  border-radius: 22px;
  border: 4px solid rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.10);
  background: #fff;
}

.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #64748b;
  font-size: 0.95rem;
  font-weight: 700;
  background: linear-gradient(180deg, #f8fafc, #e2e8f0);
}

@media (min-width: 1536px) {
  .photo {
    width: 128px;
    height: 128px;
  }
}

/* TEXT */
.identity {
  min-width: 0;
  flex: 1;
}

.name {
  font-size: clamp(1.05rem, 1.2vw, 1.3rem);
  font-weight: 900;
  color: #1e1b4b;
  margin: 0 0 8px;
  line-height: 1.25;
}

.profession {
  font-size: 0.96rem;
  color: #4f46e5;
  margin: 0;
  font-weight: 700;
  line-height: 1.5;
}

.message {
  font-size: 0.98rem;
  color: #334155;
  margin: 14px 0 0;
  line-height: 1.85;
  text-align: justify;
  text-justify: inter-word;
}

/* VIDEO */
.video {
  margin-top: 16px;
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid rgba(226, 232, 240, 1);
  background: #0f172a;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
}

.video-frame {
  width: 100%;
  height: 240px;
  display: block;
}

@media (min-width: 1536px) {
  .video-frame {
    height: 270px;
  }
}

@media (max-width: 767px) {
  .header {
    padding: 16px 8px 22px;
    margin-bottom: 20px;
  }

  .intro-card {
    border-radius: 20px;
  }

  .alumni-card {
    padding: 16px;
    border-radius: 20px;
  }

  .card-top {
    gap: 14px;
  }

  .photo {
    width: 92px;
    height: 92px;
    border-radius: 18px;
  }

  .video-frame {
    height: 200px;
  }
}

@media (max-width: 420px) {
  .card-top {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .identity {
    width: 100%;
  }

  .name,
  .profession {
    text-align: center;
  }

  .message {
    text-align: justify;
  }
}
</style>