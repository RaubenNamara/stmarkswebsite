<template>
  <div class="flex flex-col min-h-screen">
    <!-- top navbar -->
    <Navbar />

    <!-- main: flex-grow ensures this area expands so footer is pushed to bottom -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- footer in normal flow -->
    <Footer />
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
</script>