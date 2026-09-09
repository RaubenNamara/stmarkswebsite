import type { Directive } from 'vue'

/**
 * v-reveal: fades/slides an element in the first time it scrolls into view. The 'reveal'
 * (opacity:0) class is only ever added here, in mounted() - a client-only lifecycle hook that
 * never runs during vite-ssg's prerender pass - so content stays fully visible in the static
 * HTML and for any visitor whose JS fails to load; the animation is a progressive enhancement,
 * never a way to hide content.
 *
 * Usage: v-reveal (no delay) or v-reveal="120" (ms delay, for staggering a grid of siblings).
 */
export const vReveal: Directive<HTMLElement, number | undefined> = {
  mounted(el, binding) {
    el.classList.add('reveal')
    if (binding.value) el.style.transitionDelay = `${binding.value}ms`

    if (typeof IntersectionObserver === 'undefined') {
      el.classList.add('revealed')
      return
    }

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            el.classList.add('revealed')
            observer.unobserve(el)
          }
        }
      },
      { threshold: 0.15, rootMargin: '0px 0px -10% 0px' },
    )
    observer.observe(el)
  },
}
