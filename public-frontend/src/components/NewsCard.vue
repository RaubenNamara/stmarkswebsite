<script setup lang="ts">
/**
 * Modern news card, two layouts:
 *   - "overlay": the photo fills the card with the title over a gradient (Home page)
 *   - "stacked": photo on top, date / title / excerpt below (News page)
 *
 * Motion on hover (all of it switched off for visitors who prefer reduced motion):
 *   - the card lifts and its shadow deepens
 *   - the photo zooms in smoothly AND keeps drifting slowly (a gentle "Ken Burns" pan) for as long as
 *     the pointer stays on the card; it simply pauses where it is when the pointer leaves, so nothing jumps
 *   - a gold line grows along the bottom edge
 */
import { computed, ref } from 'vue'

const props = withDefaults(
  defineProps<{
    url: string
    title: string
    image?: string | null
    /** ISO date/time string from the API. */
    date?: string | null
    excerpt?: string | null
    variant?: 'overlay' | 'stacked'
  }>(),
  { image: null, date: null, excerpt: null, variant: 'overlay' },
)

const imageFailed = ref(false)

// en-GB is fixed (not the visitor's locale) so the prerendered HTML and the browser agree exactly.
const dateLabel = computed(() => {
  if (!props.date) return ''
  const d = new Date(props.date)
  return Number.isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' })
})
</script>

<template>
  <router-link :to="url" class="news-card group relative overflow-hidden rounded-3xl bg-white ring-1 ring-black/5" :class="variant === 'overlay' ? 'news-card--overlay block' : 'news-card--stacked flex flex-col'">
    <!-- photo -->
    <div class="news-card__media" :class="variant === 'stacked' ? 'relative aspect-[16/11] w-full shrink-0' : 'absolute inset-0'">
      <div class="news-card__zoom h-full w-full">
        <img
          v-if="image && !imageFailed"
          :src="image"
          :alt="title"
          loading="lazy"
          decoding="async"
          class="news-card__img h-full w-full object-cover object-top"
          @error="imageFailed = true"
        >
        <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-navy to-brand-navy-dark">
          <span class="font-display text-3xl font-extrabold text-white/20">SM</span>
        </div>
      </div>
      <div class="news-card__shade absolute inset-0" />
    </div>

    <!-- overlay layout: date chip + title on the photo -->
    <div v-if="variant === 'overlay'" class="absolute inset-0 flex flex-col justify-between p-5 sm:p-6">
      <span v-if="dateLabel" class="news-card__chip w-fit rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-semibold tracking-wide text-white backdrop-blur-md">{{ dateLabel }}</span>
      <div class="news-card__text">
        <h3 class="line-clamp-3 font-display text-lg font-bold leading-snug text-white drop-shadow-md sm:text-xl">{{ title }}</h3>
      </div>
    </div>

    <!-- stacked layout: text under the photo -->
    <div v-else class="flex flex-1 flex-col p-6">
      <span v-if="dateLabel" class="mb-3 inline-flex w-fit items-center gap-1.5 rounded-full bg-brand-navy/5 px-3 py-1 text-xs font-semibold text-brand-navy">
        <span class="h-1.5 w-1.5 rounded-full bg-brand-gold" />{{ dateLabel }}
      </span>
      <h3 class="line-clamp-3 font-display text-lg font-bold leading-snug text-gray-900 transition-colors duration-300 group-hover:text-brand-navy">{{ title }}</h3>
      <p v-if="excerpt" class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-600">{{ excerpt }}</p>
    </div>

    <!-- gold line that grows along the bottom edge -->
    <span class="news-card__line" aria-hidden="true" />
  </router-link>
</template>

<style scoped>
.news-card {
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.05), 0 10px 28px -12px rgb(23 37 84 / 0.25);
  /* opacity is listed too, because this rule replaces the site-wide scroll-in (v-reveal) transition on this element */
  transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.45s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1);
  /* keeps the rounded corners clean while children are transformed */
  isolation: isolate;
}
.news-card:hover,
.news-card:focus-visible {
  transform: translateY(-8px);
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.06), 0 28px 48px -16px rgb(23 37 84 / 0.45);
}
.news-card:focus-visible {
  outline: 3px solid #facc15;
  outline-offset: 3px;
}

/* ---- photo: smooth zoom on the wrapper, endless slow drift on the image ---- */
.news-card__media {
  overflow: hidden;
}
.news-card__zoom {
  transition: transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}
.news-card:hover .news-card__zoom,
.news-card:focus-visible .news-card__zoom {
  transform: scale(1.1);
}
.news-card__img {
  /* a little extra scale so the drift never reveals an edge */
  transform: scale(1.1);
  animation: news-drift 14s ease-in-out infinite alternate;
  animation-play-state: paused;
  will-change: transform;
}
.news-card:hover .news-card__img,
.news-card:focus-visible .news-card__img {
  animation-play-state: running;
}
@keyframes news-drift {
  0% { transform: scale(1.1) translate(-1.6%, -1.2%); }
  50% { transform: scale(1.16) translate(0.4%, 0.6%); }
  100% { transform: scale(1.1) translate(1.6%, 1.2%); }
}

/* ---- gradient over the photo ---- */
.news-card__shade {
  transition: opacity 0.5s ease;
}
.news-card--overlay .news-card__shade {
  background: linear-gradient(to top, rgb(8 14 40 / 0.88) 0%, rgb(8 14 40 / 0.45) 42%, rgb(8 14 40 / 0.05) 75%, transparent 100%);
}
.news-card--stacked .news-card__shade {
  background: linear-gradient(to top, rgb(8 14 40 / 0.25), transparent 45%);
  opacity: 0.6;
}
.news-card--stacked:hover .news-card__shade {
  opacity: 1;
}

/* ---- text ---- */
.news-card__text {
  transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}
.news-card:hover .news-card__text {
  transform: translateY(-4px);
}
.news-card__chip {
  transition: background-color 0.3s ease;
}
.news-card:hover .news-card__chip {
  background-color: rgb(250 204 21 / 0.9);
  color: #0f1a3d;
}


/* ---- gold line along the bottom ---- */
.news-card__line {
  position: absolute;
  left: 0;
  bottom: 0;
  height: 4px;
  width: 100%;
  transform: scaleX(0);
  transform-origin: left;
  background: linear-gradient(90deg, #facc15, #fde68a);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.news-card:hover .news-card__line,
.news-card:focus-visible .news-card__line {
  transform: scaleX(1);
}

/* touch screens: no hover, so skip the lift */
@media (hover: none) {
  .news-card:hover {
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .news-card,
  .news-card__zoom,
  .news-card__text,
  .news-card__line {
    transition: none;
  }
  .news-card:hover,
  .news-card:focus-visible {
    transform: none;
  }
  .news-card:hover .news-card__zoom,
  .news-card:focus-visible .news-card__zoom {
    transform: none;
  }
  .news-card__img {
    animation: none;
  }
}
</style>
