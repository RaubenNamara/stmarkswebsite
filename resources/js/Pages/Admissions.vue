<template>
 <div class="min-h-screen bg-gray-50 flex items-center justify-center px-6 py-16">

    <!-- Card -->
    <div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-2xl max-w-3xl w-full p-10 md:p-14 text-center">

      <!-- Star -->
      <div class="text-5xl mb-6">
        🌟
      </div>

      <!-- Heading -->
      <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950 leading-tight mb-6">
        Ready to Join St Mark's College Namagoma?
      </h1>

      <!-- Subtext -->
      <p class="text-lg text-gray-600 mb-10">
        Click the button below to begin your online application and take the next step toward your future.
      </p>

      <!-- Apply Button -->
      <a
        href="https://stmark.sc.ug/stmarks_admission/"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-3
               bg-yellow-400 text-blue-950 font-bold
               px-8 py-4 text-lg
               rounded-lg
               transition-all duration-300 ease-in-out
               hover:bg-yellow-300 hover:scale-105 hover:shadow-xl"
      >
        Apply Online
        <span class="text-xl">➜</span>
      </a>

    </div>

  </div>
</template>

<script>
import MainLayout from '@/Layouts/MainLayout.vue'

export default {
  layout: MainLayout
}
</script>