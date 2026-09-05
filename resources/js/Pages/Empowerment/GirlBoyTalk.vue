<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})

const { talks } = defineProps({
  talks: {
    type: Array,
    default: () => []
  }
})

function getEmbedUrl(url) {
  if (!url) return ''

  if (url.includes('youtube.com/watch')) {
    const videoId = url.split('v=')[1]
    if (!videoId) return url

    const ampersandPosition = videoId.indexOf('&')
    if (ampersandPosition !== -1) {
      return 'https://www.youtube.com/embed/' + videoId.substring(0, ampersandPosition)
    }

    return 'https://www.youtube.com/embed/' + videoId
  }

  if (url.includes('youtu.be/')) {
    const videoId = url.split('youtu.be/')[1].split(/[?&]/)[0]
    return 'https://www.youtube.com/embed/' + videoId
  }

  return url
}
</script>

<template>
  <div class="page">

    <!-- HEADER -->
    <header class="header">
      <h1>Girl-Boy Talk</h1>
      <p>Promoting respect, responsibility and healthy relationships.</p>
    </header>

    <!-- GRID -->
    <div class="grid">

      <div
        v-for="talk in talks"
        :key="talk.id"
        class="card"
      >

        <!-- IMAGE -->
        <img
          v-if="talk.image_url"
          :src="talk.image_url"
          class="image"
          alt="Talk image"
        />

        <!-- TITLE -->
        <h2 class="title">
          {{ talk.title }}
        </h2>

        <!-- CONTENT -->
        <p class="content">
          {{ talk.description }}
        </p>

        <!-- VIDEO FILE -->
        <div v-if="talk.video_url" class="video">
          <video controls class="video-frame">
            <source :src="talk.video_url" type="video/mp4">
          </video>
        </div>

        <!-- YOUTUBE -->
        <div v-if="talk.video_link" class="video">
          <iframe
            :src="getEmbedUrl(talk.video_link)"
            class="video-frame"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
          ></iframe>
        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>

/* PAGE */
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

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 60px;
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

/* GRID */

.grid {
  display: grid;
  gap: 30px;
  grid-template-columns: repeat(1, 1fr);
}

@media (min-width: 640px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(2, 1fr); /* laptop */
  }
}

@media (min-width: 1280px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1536px) {
  .grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
  }
}

@media (min-width: 1920px) {
  .grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

/* CARD */

.card {
  background: white;
  padding: 20px;
  border: 1px solid #e5e7eb;
  transition: 0.3s;
  display: flex;
  flex-direction: column;
}

@media (min-width: 1536px) {
  .card {
    padding: 25px;
  }
}

.card:hover {
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* IMAGE */

.image {
  width: 100%;
  height: 220px;
  object-fit: cover;
  margin-bottom: 15px;
}

@media (min-width: 1536px) {
  .image {
    height: 260px;
  }
}

/* TEXT */

.title {
  font-size: 22px;
  font-weight: 700;
  color: #3730a3;
  margin-bottom: 10px;
}

@media (min-width: 1536px) {
  .title {
    font-size: 24px;
  }
}

.content {
  color: #444;
  line-height: 1.7;
  text-align: justify;
  flex: 1;
}

/* VIDEO */

.video {
  margin-top: 18px;
}

.video-frame {
  width: 100%;
  height: 220px;
}

@media (min-width: 1536px) {
  .video-frame {
    height: 260px;
  }
}

/* MOBILE */

@media (max-width: 768px) {
  .page {
    padding: 40px 15px;
  }
}

</style>