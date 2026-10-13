<script setup lang="ts">
// Floating WhatsApp button, shown on every page (mounted once from App.vue). Bottom-left, so the
// chat assistant keeps the bottom-right corner. z-30 keeps it above page content but BELOW the sticky
// header (z-50) and the full-screen mobile menu (z-40), so the menu covers it instead of it floating over the links.
</script>

<template>
  <a
    href="https://wa.me/256775831844"
    target="_blank"
    rel="noopener"
    aria-label="Chat with us on WhatsApp"
    class="whatsapp-float fixed bottom-6 left-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform duration-300 hover:scale-110"
    style="bottom: max(1.5rem, env(safe-area-inset-bottom)); left: max(1.5rem, env(safe-area-inset-left))"
  >
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-8 w-8" fill="currentColor" aria-hidden="true">
      <path d="M16.004 3C9.377 3 4 8.377 4 15.004c0 2.65.864 5.1 2.33 7.086L4.999 28l6.09-1.6a11.94 11.94 0 0 0 4.915 1.045h.005c6.627 0 12.004-5.377 12.004-12.004C28.013 8.377 22.636 3 16.004 3zm7.03 17.13c-.294.828-1.457 1.516-2.393 1.716-.638.135-1.472.243-4.283-.92-3.594-1.485-5.906-5.128-6.086-5.365-.174-.237-1.457-1.94-1.457-3.7 0-1.76.923-2.622 1.25-2.98.328-.36.717-.45.956-.45.24 0 .478.002.687.013.22.011.516-.084.808.617.294.7.998 2.42 1.086 2.596.09.176.15.383.03.62-.12.237-.18.383-.36.59-.18.208-.376.464-.537.624-.18.176-.367.367-.158.72.21.353.933 1.539 2.004 2.492 1.376 1.225 2.536 1.605 2.887 1.786.35.18.556.15.762-.09.207-.24.884-1.03 1.12-1.383.238-.353.478-.294.807-.176.328.117 2.086.984 2.443 1.163.357.18.596.267.685.416.09.15.09.87-.203 1.7z" />
    </svg>
  </a>
</template>

<style scoped>
.whatsapp-float {
  animation: whatsappPulse 2.5s ease-in-out infinite;
}
@keyframes whatsappPulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.5);
  }
  50% {
    box-shadow: 0 0 0 14px rgba(37, 211, 102, 0);
  }
}
@media (prefers-reduced-motion: reduce) {
  .whatsapp-float {
    animation: none;
  }
}
</style>
