<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'

defineOptions({
  layout: MainLayout
})
</script>

<template>
  <div class="bg-gray-50 min-h-screen py-16 md:py-20 2xl:py-28">

    <!-- CONTAINER -->
    <div class="max-w-[1400px] 2xl:max-w-[1700px] 3xl:max-w-[1900px] mx-auto px-6 md:px-10 2xl:px-16">

      <!-- HEADER -->
      <div class="text-center mb-20 2xl:mb-28">
        <h1 class="text-4xl md:text-5xl 2xl:text-6xl font-bold text-[#07203a] mb-6">
          Our Curriculum
        </h1>

        <p class="text-gray-600 text-lg 2xl:text-xl max-w-3xl 2xl:max-w-4xl mx-auto leading-relaxed">
          All subjects are delivered under the Competency-Based Curriculum,
          designed to develop knowledge, practical skills, values, creativity,
          and critical thinking for academic excellence and lifelong success.
        </p>
      </div>

      <!-- ================= O LEVEL ================= -->
      <section class="mb-24 2xl:mb-32">

        <h2 class="text-3xl md:text-4xl 2xl:text-5xl font-bold text-[#0b2f56] mb-12 text-center">
          O-Level (Ordinary Level)
        </h2>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">

          <!-- SCIENCES -->
          <div class="card">
            <h3 class="card-title">Sciences</h3>
            <ul class="card-list">
              <li>• Mathematics</li>
              <li>• Chemistry</li>
              <li>• Biology</li>
              <li>• Physics</li>
            </ul>
          </div>

          <!-- VOCATIONAL -->
          <div class="card">
            <h3 class="card-title">Vocational Subjects</h3>
            <ul class="card-list">
              <li>• Entrepreneurship</li>
              <li>• ICT</li>
              <li>• Art and Design</li>
              <li>• Technical Drawing</li>
              <li>• Physical Education</li>
            </ul>
          </div>

          <!-- LANGUAGES -->
          <div class="card">
            <h3 class="card-title">Languages</h3>
            <ul class="card-list">
              <li>• Kiswahili</li>
              <li>• Luganda</li>
              <li>• Literature</li>
              <li>• English</li>
            </ul>
          </div>

          <!-- HUMANITIES -->
          <div class="card sm:col-span-2 lg:col-span-3 xl:col-span-4 2xl:col-span-5">
            <h3 class="card-title">Humanities</h3>

            <ul class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 text-gray-700">
              <li>• Religious Education</li>
              <li>• Geography</li>
              <li>• History</li>
              <li>• Islamic Religious Education (IRE)</li>
            </ul>
          </div>

        </div>

      </section>

      <!-- ================= A LEVEL ================= -->
      <section>

        <h2 class="text-3xl md:text-4xl 2xl:text-5xl font-bold text-[#0b2f56] mb-12 text-center">
          A-Level (Advanced Level)
        </h2>

        <div class="bg-white rounded-2xl shadow-md p-8 md:p-10 2xl:p-14">

          <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">

            <div class="subject">Physics</div>
            <div class="subject">Chemistry</div>
            <div class="subject">Biology</div>
            <div class="subject">Mathematics</div>
            <div class="subject">Geography</div>
            <div class="subject">History</div>
            <div class="subject">CRE / Divinity</div>
            <div class="subject">Technical Drawing</div>
            <div class="subject">Kiswahili</div>
            <div class="subject">Luganda</div>
            <div class="subject">Entrepreneurship</div>
            <div class="subject">Literature</div>
            <div class="subject">Fine Art</div>
            <div class="subject">Economics</div>

          </div>

        </div>

      </section>

    </div>
  </div>
</template>

<style scoped>

/* CARD STYLE */
.card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  transition: 0.3s;
}

@media (min-width: 1536px) {
  .card {
    padding: 2rem;
  }
}

.card:hover {
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.card-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1d4ed8;
  margin-bottom: 1rem;
}

@media (min-width: 1536px) {
  .card-title {
    font-size: 1.4rem;
  }
}

.card-list {
  color: #374151;
  line-height: 1.6;
}

.card-list li {
  margin-bottom: 0.4rem;
}

/* SUBJECT TAGS */
.subject {
  background: #f3f4f6;
  padding: 0.5rem 0.8rem;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  transition: 0.2s;
  text-align: center;
}

@media (min-width: 1536px) {
  .subject {
    font-size: 1rem;
    padding: 0.7rem 1rem;
  }
}

.subject:hover {
  background: #e5e7eb;
}

</style>