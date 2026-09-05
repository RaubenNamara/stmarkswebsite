<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Head } from '@inertiajs/vue3'
import RelatedArticles from '@/Components/CampusVoices/RelatedArticles.vue'

defineOptions({ layout: MainLayout })

const props = defineProps({
  article: Object,
  previousArticle: Object,
  nextArticle: Object,
  relatedArticles: Array,
})

/*
|--------------------------------------------------------------------------
| SHARE FUNCTIONALITY
|--------------------------------------------------------------------------
*/
function shareArticle(platform) {
  const url = window.location.href
  const title = props.article.title

  let shareUrl = ''

  switch (platform) {
    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`
      break
    case 'twitter':
      shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`
      break
    case 'linkedin':
      shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`
      break
    case 'whatsapp':
      shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`
      break
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400')
  }
}

function copyLink() {
  navigator.clipboard.writeText(window.location.href)
  alert('Link copied to clipboard!')
}
</script>

<template>
  <Head :title="article.title + ' - Campus Voices'" />

  <div class="min-h-screen bg-white">
    
    <!-- ================= ARTICLE CONTENT ================= -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-12">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 xl:gap-10">
        
        <!-- Main Content -->
        <div class="lg:col-span-2">
          
          <!-- Breadcrumb -->
          <nav class="mb-6 text-sm">
            <a :href="route('explore.campus-voices')" class="text-gray-600 hover:text-gray-900 font-medium">
              Campus Voices
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-500">{{ article.title }}</span>
          </nav>

          <!-- Image Card -->
          <div v-if="article.image_url" class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6 mb-8">
            <div class="bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center">
              <img
                :src="article.image_url"
                class="w-full h-64 sm:h-80 lg:h-96 object-contain"
              />
            </div>
          </div>

          <!-- Title Card -->
          <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-8 mb-6">
            <span v-if="article.category" class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold mb-4 border border-blue-300">
              {{ article.category }}
            </span>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
              {{ article.title }}
            </h1>
            <p class="text-lg text-gray-700 mb-4">
              By <span class="font-semibold text-blue-900">{{ article.student_name }}</span>
            </p>
            
            <!-- Article Meta -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 pt-4 border-t-2 border-gray-200">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ article.published_at }}</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ article.reading_time }}</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ article.views }} views</span>
              </div>
            </div>
          </div>

          <!-- Share Buttons -->
          <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6 mb-6">
            <p class="text-sm font-semibold text-gray-700 mb-4">Share this article:</p>
            <div class="flex flex-wrap gap-3">
              <button
                @click="shareArticle('facebook')"
                class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
              </button>
              <button
                @click="shareArticle('twitter')"
                class="flex items-center gap-2 bg-sky-500 text-white px-4 py-2 rounded-lg hover:bg-sky-600 transition"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                Twitter
              </button>
              <button
                @click="shareArticle('linkedin')"
                class="flex items-center gap-2 bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
                LinkedIn
              </button>
              <button
                @click="shareArticle('whatsapp')"
                class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.237-.669-.242-.173-.005-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp
              </button>
              <button
                @click="copyLink"
                class="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Copy Link
              </button>
            </div>
          </div>

          <!-- Article Body -->
          <div class="prose prose-lg max-w-none bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-8 mb-8">
            <div v-html="article.content" class="text-gray-800 leading-relaxed text-justify"></div>
          </div>

          <!-- Navigation -->
          <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6 mb-8">
            <div class="flex justify-between items-center">
              <div v-if="previousArticle" class="flex-1">
                <a
                  :href="route('campus-voices.show', previousArticle.slug)"
                  class="flex items-center gap-3 text-gray-600 hover:text-gray-900 transition group"
                >
                  <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Previous</p>
                    <p class="font-medium text-gray-900">{{ previousArticle.title }}</p>
                    <p class="text-sm text-gray-500">{{ previousArticle.student_name }}</p>
                  </div>
                </a>
              </div>

              <div v-if="nextArticle" class="flex-1 text-right">
                <a
                  :href="route('campus-voices.show', nextArticle.slug)"
                  class="flex items-center justify-end gap-3 text-gray-600 hover:text-gray-900 transition group"
                >
                  <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Next</p>
                    <p class="font-medium text-gray-900">{{ nextArticle.title }}</p>
                    <p class="text-sm text-gray-500">{{ nextArticle.student_name }}</p>
                  </div>
                  <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <!-- Back to List -->
          <div class="text-center">
            <a
              :href="route('explore.campus-voices')"
              class="inline-flex items-center gap-2 bg-blue-900 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-800 transition border-2 border-blue-900"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Campus Voices
            </a>
          </div>

        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">About the Author</h3>
            <div class="flex items-center gap-4 mb-4">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-2xl font-bold text-blue-900 border-2 border-blue-300">
                {{ article.student_name.charAt(0) }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ article.student_name }}</p>
              </div>
            </div>
            <ul v-if="article.author_bio" class="text-sm text-gray-700 leading-relaxed space-y-3 text-center">
              <li v-for="(line, index) in article.author_bio.split('\n').filter(line => line.trim())" :key="index" class="flex items-center justify-center">
                <span class="text-blue-900 font-semibold mr-2">•</span>
                <span>{{ line }}</span>
              </li>
            </ul>
          </div>

          <div v-if="relatedArticles.length > 0" class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Related Articles</h3>
            <div class="space-y-4">
              <a
                v-for="related in relatedArticles"
                :key="related.id"
                :href="route('campus-voices.show', related.slug)"
                class="block group"
              >
                <div class="h-32 bg-gray-50 rounded-lg mb-3 overflow-hidden border-2 border-gray-200">
                  <img
                    v-if="related.image_url"
                    :src="related.image_url"
                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
                <p class="text-sm text-gray-600 mb-1">{{ related.student_name }}</p>
                <h4 class="font-medium text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
                  {{ related.title }}
                </h4>
              </a>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
}

.prose :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 1.5rem 0;
}

.prose :deep(h2) {
  font-size: 1.875rem;
  font-weight: 700;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #1f2937;
}

.prose :deep(p) {
  margin-bottom: 1.25rem;
  line-height: 1.75;
}

.prose :deep(ul),
.prose :deep(ol) {
  margin-left: 1.5rem;
  margin-bottom: 1.25rem;
}

.prose :deep(li) {
  margin-bottom: 0.5rem;
}

.prose :deep(blockquote) {
  border-left: 4px solid #3b82f6;
  padding-left: 1rem;
  margin: 1.5rem 0;
  font-style: italic;
  color: #6b7280;
}
</style>
