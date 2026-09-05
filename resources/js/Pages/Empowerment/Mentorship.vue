<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const props = defineProps({
  mentorships: {
    type: Array,
    default: () => []
  }
})

/* YOUTUBE EMBED */
function embedUrl(link) {
  if (!link) return ''

  try {
    if (link.includes('youtube.com/watch')) {
      const url = new URL(link)
      const videoId = url.searchParams.get('v')
      return videoId ? `https://www.youtube.com/embed/${videoId}` : link
    }

    if (link.includes('youtu.be/')) {
      const id = link.split('youtu.be/')[1].split(/[?&]/)[0]
      return `https://www.youtube.com/embed/${id}`
    }

    return link
  } catch {
    return link
  }
}
</script>

<template>
  <div class="page">

    <!-- HEADER -->
    <header class="header">
      <h1>Mentorship Programme</h1>
      <p>
        Guiding students toward leadership, responsibility and success.
      </p>
    </header>

    <!-- CARDS -->
    <div
      v-for="mentorship in mentorships"
      :key="mentorship.id"
      class="card"
    >

      <div class="row">

        <!-- MEDIA -->
        <div class="media">

          <!-- IMAGE -->
          <img
            v-if="mentorship.image_url"
            :src="mentorship.image_url"
            class="image"
            alt="Mentorship Image"
          />

          <!-- VIDEO FILE -->
          <video
            v-else-if="mentorship.video_url"
            controls
            class="video"
          >
            <source :src="mentorship.video_url" />
          </video>

          <!-- YOUTUBE -->
          <iframe
            v-else-if="mentorship.video_link"
            class="video"
            :src="embedUrl(mentorship.video_link)"
            frameborder="0"
            allowfullscreen
          ></iframe>

        </div>

        <!-- TEXT -->
        <div class="text">

          <h2 class="title">
            {{ mentorship.title }}
          </h2>

          <p v-if="mentorship.caption" class="caption">
            {{ mentorship.caption }}
          </p>

          <p class="content">
            {{ mentorship.description }}
          </p>

        </div>

      </div>

    </div>

    <!-- EMPTY STATE -->
    <div
      v-if="!mentorships.length"
      class="empty"
    >
      No mentorship sessions available yet.
    </div>

  </div>
</template>

<style scoped>

/* ================= PAGE ================= */
.page {
  max-width: 1400px;
  margin: auto;
  padding: 60px 20px;
}

@media (min-width: 1536px) {
  .page {
    max-width: 1700px;
    padding: 80px 40px;
  }
}

@media (min-width: 1920px) {
  .page {
    max-width: 1900px;
  }
}

/* ================= HEADER ================= */
.header {
  text-align: center;
  margin-bottom: 70px;
}

.header h1 {
  font-size: 38px;
  font-weight: 800;
  color: #3730a3;
}

@media (min-width: 1536px) {
  .header h1 {
    font-size: 48px;
  }
}

.header p {
  color: #555;
  font-size: 18px;
  margin-top: 10px;
}

/* ================= CARD ================= */
.card {
  background: white;
  padding: 25px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  margin-bottom: 50px;
  transition: 0.3s;
}

@media (min-width: 1536px) {
  .card {
    padding: 35px;
  }
}

.card:hover {
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* ================= ROW ================= */
.row {
  display: grid;
  gap: 30px;
  align-items: center;
}

@media (min-width: 768px) {
  .row {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* 🔥 BIG SCREEN PERFECT LAYOUT */
@media (min-width: 1536px) {
  .row {
    grid-template-columns: 1fr 2fr;
    gap: 50px;
  }
}

/* ================= MEDIA ================= */
.media {
  width: 100%;
}

/* IMAGE */
.image {
  width: 100%;
  height: 260px;
  object-fit: cover;
}

@media (min-width: 1024px) {
  .image {
    height: 320px;
  }
}

@media (min-width: 1536px) {
  .image {
    height: 360px;
  }
}

/* VIDEO */
.video {
  width: 100%;
  height: 260px;
}

@media (min-width: 1024px) {
  .video {
    height: 320px;
  }
}

@media (min-width: 1536px) {
  .video {
    height: 360px;
  }
}

/* ================= TEXT ================= */
.text {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* TITLE */
.title {
  font-size: 24px;
  font-weight: 700;
  color: #3730a3;
  margin-bottom: 10px;
}

@media (min-width: 1536px) {
  .title {
    font-size: 30px;
  }
}

/* CAPTION */
.caption {
  color: #6b7280;
  font-style: italic;
  margin-bottom: 15px;
}

/* CONTENT */
.content {
  color: #444;
  font-size: 17px;
  line-height: 1.8;
  text-align: justify;
}

@media (min-width: 1536px) {
  .content {
    font-size: 18px;
  }
}

/* ================= EMPTY ================= */
.empty {
  text-align: center;
  color: #6b7280;
  font-size: 18px;
  padding: 60px 0;
}

/* ================= MOBILE ================= */
@media (max-width: 768px) {
  .page {
    padding: 40px 15px;
  }
}

</style>