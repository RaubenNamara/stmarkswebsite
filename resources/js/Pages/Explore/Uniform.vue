<template>
  <div class="max-w-[900px] md:max-w-[1100px] lg:max-w-[1300px] xl:max-w-[1500px] 2xl:max-w-[1700px] mx-auto py-12 sm:py-16 px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <header class="text-center mb-12 sm:mb-14">
      <h1
        class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold 
               bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-600 
               bg-clip-text text-transparent"
      >
        School Uniform
      </h1>

      <p class="mt-4 text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
        Our uniform reflects discipline, identity, and excellence.
      </p>
    </header>

    <!-- Academic Uniform -->
    <section class="mb-14 lg:mb-16">
      <h2 class="section-title">Academic Uniform</h2>

      <div class="card grid md:grid-cols-2 xl:grid-cols-3 gap-8">

        <!-- O-Level -->
        <div>
          <h3 class="sub-title">O-Level Students</h3>
          <ul class="uniform-list">
            <li>✔ Cream Shirts / Blouses</li>
            <li>✔ Navy Blue Trousers / Skirts</li>
          </ul>
        </div>

        <!-- A-Level -->
        <div>
          <h3 class="sub-title">A-Level Students</h3>
          <ul class="uniform-list">
            <li>✔ Blue Shirts / Blouses</li>
            <li>✔ Navy Blue Trousers / Skirts</li>
          </ul>
        </div>

        <!-- Sweater -->
        <div>
          <h3 class="sub-title">School Sweater</h3>
          <ul class="uniform-list">
            <li>✔ Navy Blue Sweater</li>
            <li>✔ Light Blue Stripes at the Neck and Sleeves</li>
          </ul>
        </div>

      </div>
    </section>

    <!-- Out of Class -->
    <section class="mb-14 lg:mb-16">
      <h2 class="section-title">Out-of-Class Uniform</h2>

      <div class="card grid md:grid-cols-2 gap-6">
        <ul class="uniform-list">
          <li>✔ Plain Red T-Shirts (Different colors per class)</li>
          <li>✔ Black Skirts / Trousers</li>
        </ul>

        <ul class="uniform-list">
          <li>✔ Students should bring only skirts/trousers</li>
          <li>✔ Strict adherence required</li>
        </ul>
      </div>
    </section>

    <!-- Notice -->
    <section class="mb-14 lg:mb-16">
      <div class="bg-red-50 border-l-4 border-red-600 p-6 sm:p-8 rounded-xl shadow-md max-w-4xl mx-auto">
        <h3 class="text-lg sm:text-xl font-bold text-red-700 mb-3">
          Important Notice
        </h3>
        <p class="text-gray-700 text-base sm:text-lg">
          No other form of dressing is allowed in school apart from that indicated above.
        </p>
      </div>
    </section>

    <!-- Package -->
    <section>
      <h2 class="section-title">Uniform Package (Available at School)</h2>

      <div class="card grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <ul class="uniform-list">
          <li>✔ School Sweater</li>
          <li>✔ 2 Shirts / Blouses</li>
        </ul>

        <ul class="uniform-list">
          <li>✔ 2 Trousers / Skirts</li>
          <li>✔ Sunday Wear</li>
        </ul>

        <ul class="uniform-list">
          <li>✔ Sports Wear</li>
          <li>✔ 3 T-Shirts for Out-of-Class Wear</li>
        </ul>
      </div>
    </section>

  </div>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})
</script>

<style scoped>
.section-title {
  @apply text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-900 mb-6;
}

.sub-title {
  @apply text-lg sm:text-xl font-semibold text-blue-800 mb-3;
}

.card {
  @apply bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 
         p-6 sm:p-8 lg:p-10 
         rounded-2xl shadow-lg border border-indigo-200;
}

.uniform-list {
  @apply space-y-3 text-gray-700 text-sm sm:text-base lg:text-lg;
}
</style>