<template>
  <div>
    <!-- ========== FULL WIDTH HERO SLIDER ========== -->
    <section
      class="relative w-full overflow-hidden"
      @mouseenter="pause"
      @mouseleave="play"
      @touchstart.passive="onTouchStart"
      @touchend.passive="onTouchEnd"
      aria-roledescription="carousel"
    >
      <div
        class="relative h-[55vh] min-h-[320px] md:h-[65vh] lg:h-[80vh] xl:h-[75vh] 2xl:h-[70vh]"
      >
        <template v-for="(slide, i) in computedSlides" :key="i">
          <div
            class="absolute inset-0 transition-opacity duration-700 ease-in-out"
            :class="current === i ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none'"
            role="group"
            :aria-roledescription="'slide'"
            :aria-label="`${i + 1} of ${computedSlides.length}`"
          >
            <img
              v-if="slide.type === 'image' && slide.image_url"
              :src="slide.image_url"
              :alt="slide.title"
              class="w-full h-full object-cover will-change-transform transition-transform duration-1000"
              :class="current === i ? animationClass(slide.effect) : 'inactive-img'"
            />

            <video
              v-else-if="slide.type === 'video' && slide.video_url"
              :src="slide.video_url"
              autoplay
              muted
              loop
              playsinline
              class="w-full h-full object-cover"
            ></video>

            <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/40 pointer-events-none"></div>

            <div class="absolute inset-0 z-20 flex items-center justify-center text-center px-6 lg:px-12 xl:px-16 2xl:px-20">
              <div
                class="text-white max-w-3xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-[1100px] transform transition-all duration-700"
                :class="current === i ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
              >
                <h2 class="site-heading mb-4">
                  {{ slide.title }}
                </h2>
                <p class="text-base md:text-lg lg:text-xl mb-6 opacity-90">
                  {{ slide.subtitle }}
                </p>
                <a
                  :href="slide.ctaUrl || '#'"
                  class="inline-block hero-cta px-5 py-2 rounded-md shadow text-white font-medium transition"
                >
                  {{ slide.ctaText || 'Learn More' }}
                </a>
              </div>
            </div>
          </div>
        </template>
      </div>

      <button
        @click="goPrevAndReset"
        class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-md hover:bg-white/30 text-white p-3 rounded-full shadow"
        aria-label="Previous slide"
        type="button"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <button
        @click="goNextAndReset"
        class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-md hover:bg-white/30 text-white p-3 rounded-full shadow"
        aria-label="Next slide"
        type="button"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex gap-3">
        <button
          v-for="(slide, i) in computedSlides"
          :key="i"
          @click="goTo(i)"
          :class="['rounded-full transition-all duration-300 focus:outline-none', current === i ? 'w-10 h-2 bg-white' : 'w-4 h-2 bg-white/40']"
          :aria-label="`Go to slide ${i + 1}`"
          type="button"
        ></button>
      </div>
    </section>

    <!-- ========== BACKGROUND (IMAGE LEFT + TEXT RIGHT) ========== -->
    <section class="container-wide mt-6 relative z-20">
      <div class="decorative-panel overflow-hidden rounded-2xl shadow-2xl border bg-white/95">
        <div class="grid grid-cols-1 lg:grid-cols-2 items-start">
          <div class="left-image relative h-56 md:h-72 lg:h-[420px] xl:h-[480px] w-full overflow-hidden group">
            <img
              src="/images/home.jpg"
              alt="St. Mark’s College Namagoma - campus view"
              class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-105"
            />
            <div class="absolute inset-4 rounded-xl pointer-events-none decorative-frame"></div>
            <div class="absolute inset-8 rounded-lg pointer-events-none inner-accent"></div>
          </div>

          <div class="p-6 md:p-8 lg:p-10 xl:p-12 flex flex-col justify-center min-h-48 lg:min-h-[420px]">
            <h2 class="welcome-heading mb-4">Welcome to St. Mark’s College Namagoma</h2>

            <p v-if="!backgroundExpanded" class="text-gray-700 leading-relaxed mb-4">
              {{ backgroundIntro }}
            </p>

            <div v-if="!backgroundExpanded" class="mt-auto hidden md:block">
              <button
                @click="toggleBackground"
                :aria-expanded="backgroundExpanded.toString()"
                class="inline-flex items-center gap-2 readmore-btn px-5 py-2 rounded-md shadow-md transition"
                type="button"
              >
                Read More
              </button>
            </div>

            <div v-else class="text-gray-700 leading-relaxed space-y-2">
              <p class="mb-0">{{ smallPreview }}</p>
            </div>
          </div>
        </div>

        <div class="md:hidden px-6 sm:px-8 flex justify-center mt-4 pb-6">
          <button
            v-if="!backgroundExpanded"
            @click="toggleBackground"
            :aria-expanded="backgroundExpanded.toString()"
            class="inline-flex items-center gap-2 readmore-btn px-5 py-2 rounded-md shadow-md transition"
            type="button"
          >
            Read More
          </button>
        </div>

        <div
          v-if="backgroundExpanded"
          class="expanded-content px-6 md:px-10 py-6 border-t border-indigo-50 bg-gradient-to-b from-white to-white/95"
        >
          <div class="max-w-6xl 2xl:max-w-7xl mx-auto text-gray-700 leading-relaxed">
            <div class="justified-text space-y-4 text-gray-700">
              <p>
                St. Mark’s College Namagoma is a high quality private secondary school founded in 2003. It is a mixed boarding school offering Arts and Sciences at both “O” and “A” Level. The College is owned by experienced and well-educated individuals with a strong commitment to excellence in academics and post-school life.
              </p>

              <p>
                Their high achievements in academics enable the Directors to be well versed with the students and school needs, and they are ready and capable of offering the best environment for student learning.
              </p>

              <p>
                The College has a seasoned Board of Governors, including Educationists, Bankers and Managers at high levels in Ugandan society. The Head Teacher and the entire staff are carefully selected to meet the high standards of the College.
              </p>

              <p>
                The College is duly licensed and registered by the Ministry of Education and Sports. Its registration and UNEB centre numbers are PSS/S/261 and U1664 respectively. Since opening its doors on 10th February 2003, the College has achieved tremendous success in student recruitment and retention, and now accommodates over 2,000 students in both ‘O’ and ‘A’ Level sections.
              </p>

              <h4 class="font-bold text-lg mt-6">Location</h4>

              <p>
                The College campus is located on 30 acres of land at Namagoma, 10 miles along the Kampala–Masaka Road, just 1 km off the main road. Neighboring well-established and highly successful schools like King’s College Budo, Trinity College Nabbingo and St. Lawrence Colleges – “the Massachusetts Corridor of Uganda” – the environment provides healthy competition for high academic achievement.
              </p>

              <p>
                In addition, the College is located away from the hustle and bustle of town, creating a quiet environment conducive for academic concentration, yet it is easily accessible.
              </p>

              <h4 class="font-bold text-lg mt-6">Education Philosophy</h4>

              <p>
                Our philosophy is to value all students for their individual abilities and special talents. This enables students to grow into mature, compassionate, honest and honorable individuals.
              </p>

              <ul class="list-disc pl-6 space-y-2">
                <li>Are resourceful and responsible citizens</li>
                <li>Promote national unity and harmonious communities</li>
                <li>Display a high sense of discipline, ethical and spiritual values</li>
                <li>Take collective responsibility and respect public property</li>
                <li>Have strong analytical and problem-solving skills with positive attitudes toward work</li>
              </ul>

              <p>
                Our vision is to be a leading secondary school in Uganda and the East African region.
              </p>

              <p>
                Programs at St. Mark’s College are stimulating, rewarding and forward-looking. They focus on building a holistic individual through academic excellence, character formation, leadership, life skills and development of talents.
              </p>

              <p>
                All interested parents and students from across East Africa are warmly welcome to become part of the St. Mark’s College fraternity.
              </p>

              <p class="italic font-semibold">
                “To Be, Not To Seem.”
              </p>
            </div>

            <div class="flex justify-start mt-6">
              <button
                @click="toggleBackground"
                class="inline-flex items-center gap-2 bg-white border border-indigo-300 hover:bg-indigo-50 text-indigo-700 px-5 py-2 rounded-md shadow-sm transition"
                type="button"
              >
                Show Less
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= SCHOOL LEADERSHIP ================= -->
    <section class="leadership-section py-6">
      <div class="container-wide">
        <div class="text-center mb-16">
          <h3 class="section-title text-3xl md:text-4xl font-extrabold">
            School Leadership
          </h3>
          <div class="leadership-divider"></div>
          <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
            Visionary leaders guiding academic excellence, discipline and holistic student development.
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
          <article class="leadership-card">
            <div class="leadership-avatar">
              <img src="/images/director1.jpg" alt="Owek Ddamilira Daniel" />
            </div>
            <div class="leadership-content">
              <h4 class="leadership-name">Owek Ddamulira Daniel</h4>
              <p class="leadership-role">Director</p>
              <p class="leadership-text">
                A visionary education leader committed to institutional growth, integrity and academic excellence.
              </p>
              <a href="/director1" class="leadership-btn">Read More</a>
            </div>
          </article>

          <article class="leadership-card">
            <div class="leadership-avatar">
              <img src="/images/director2.jpg" alt="Con Alice Ddamulira" />
            </div>
            <div class="leadership-content">
              <h4 class="leadership-name">Canon Alice Ddamulira</h4>
              <p class="leadership-role">Director</p>
              <p class="leadership-text">
                Dedicated to nurturing discipline, excellence and strong moral values within the school community.
              </p>
              <a href="/director2" class="leadership-btn">Read More</a>
            </div>
          </article>

          <article class="leadership-card">
            <div class="leadership-avatar">
              <img src="/images/hm.jpg" alt="Wabwire Joseph" />
            </div>
            <div class="leadership-content">
              <h4 class="leadership-name">Wabwire Joseph</h4>
              <p class="leadership-role">Head Teacher</p>
              <p class="leadership-text">
                Provides strong academic leadership ensuring holistic development and consistent performance.
              </p>
              <a href="/headteacher" class="leadership-btn-alt">Read More</a>
            </div>
          </article>
        </div>
      </div>
    </section>

 <!-- ================= LATEST NEWS & EVENTS ================= -->
<section class="container-wide pt-12 pb-4">
  <div class="flex items-center justify-between mb-6">
    <h3 class="section-title text-2xl md:text-3xl font-extrabold text-gray-900">
      Latest News &amp; Events
    </h3>
  </div>

  <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
    
    <!-- LOADING -->
    <template v-if="loading">
      <div 
        v-for="n in 3" 
        :key="n" 
        class="animate-pulse bg-white rounded-lg shadow p-4 h-64 border border-gray-100">
      </div>
    </template>

    <!-- NEWS ITEMS -->
    <template v-else-if="newsItems && newsItemsShown.length">
      <article
        v-for="item in newsItemsShown"
        :key="item.id"
        class="relative bg-white rounded-lg overflow-hidden border border-gray-100 shadow-sm hover:shadow-lg transform transition hover:-translate-y-1 group"
        role="article"
        :aria-labelledby="`news-title-${item.id}`"
      >

        <div class="relative h-52 md:h-60 lg:h-64 xl:h-72 2xl:h-80 overflow-hidden">

          <!-- IMAGE -->
          <img
            :src="item.image_url || '/images/placeholder-news.jpg'"
            :alt="item.title"
            class="w-full h-full object-cover object-[center_top] transition-transform duration-700 ease-in-out group-hover:scale-110"
          />

          <!-- OVERLAY -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent"></div>

          <!-- CONTENT -->
          <div class="absolute inset-0 p-6 text-white z-10 flex flex-col justify-between">

            <!-- TOP: DATE + READ MORE -->
            <div class="flex items-center justify-between">

              <!-- DATE -->
              <div
                v-if="item.date || item.published_at"
                class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/20 text-white px-3 py-1.5 rounded-full text-xs md:text-sm font-semibold shadow-lg"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z"
                  />
                </svg>
                {{ beautifulDate(item.date || item.published_at) }}
              </div>

              <!-- READ MORE -->
              <a
                :href="item.url || `/news/${item.slug || item.id}`"
                class="bg-gradient-to-r from-blue-600 to-indigo-800 text-white px-4 py-2 rounded-md font-semibold shadow-lg opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500"
              >
                Read More
              </a>

            </div>

            <!-- BOTTOM: TITLE -->
            <div>
              <h4 
                :id="`news-title-${item.id}`" 
                class="news-heading justified-title"
              >
                <a :href="item.url || `/news/${item.slug || item.id}`">
                  {{ item.title }}
                </a>
              </h4>
            </div>

          </div>
        </div>
      </article>
    </template>

    <!-- EMPTY -->
    <template v-else>
      <div class="col-span-full bg-white rounded-lg shadow p-6 text-center border border-gray-100">
        <p class="text-gray-700">No news or events yet.</p>
      </div>
    </template>

  </div>
</section>

<!-- ================= FEATURED POSTS ================= -->
<section class="container-wide py-12">
  <div class="max-w-[1700px] mx-auto">

    <div class="featured-wrapper rounded-2xl p-6 md:p-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-8 gap-4">
        <div>
          <h3 class="section-title text-2xl md:text-3xl font-extrabold text-indigo-900">
            Featured Posts
          </h3>
        </div>

        <div v-if="featuredPosts.length > 1" class="hidden sm:flex items-center gap-3 flex-shrink-0">
          <button
            @click="scrollFeatured(-1)"
            class="featured-nav-btn"
            aria-label="Scroll featured posts left"
            type="button"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="scrollFeatured(1)"
            class="featured-nav-btn"
            aria-label="Scroll featured posts right"
            type="button"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Sliding track -->
      <div
        v-if="featuredPosts.length"
        ref="featuredScrollEl"
        class="featured-track"
        @mouseenter="pauseFeatured"
        @mouseleave="resumeFeatured"
        @touchstart.passive="pauseFeatured"
        @touchend.passive="scheduleResumeFeatured"
      >
        <article
          v-for="entry in featuredPostsLoop"
          :key="entry.key"
          class="featured-card group"
        >
          <a :href="entry.post.url || `/news/${entry.post.slug || entry.post.id}`" class="block h-full">
            <div class="featured-card-img">
              <img
                :src="entry.post.image_url || '/images/placeholder-news.jpg'"
                :alt="entry.post.title"
                loading="lazy"
                @error="onFeaturedImgError"
              />
              <span v-if="entry.post.date || entry.post.created_at" class="featured-date-badge">
                {{ beautifulDate(entry.post.date || entry.post.created_at) }}
              </span>
            </div>

            <div class="featured-card-body">
              <h4 class="featured-title line-clamp-2">{{ entry.post.title }}</h4>
              <p v-if="entry.post.excerpt" class="featured-excerpt line-clamp-2">{{ entry.post.excerpt }}</p>
              <span class="featured-readmore">
                Read More
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </span>
            </div>
          </a>
        </article>
      </div>

      <p v-else class="text-gray-600 text-center py-6">No featured posts yet.</p>
    </div>

  </div>
</section>
    <!-- ================= MOTTO / CORE VALUES / WHY CHOOSE ================= -->
    <section class="pt-16 pb-6 bg-gradient-to-b from-indigo-50 to-white">
      <div class="container-wide">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="group rounded-2xl p-8 bg-white border-2 border-indigo-100 shadow-lg hover:shadow-2xl transition duration-300">
            <h3 class="text-2xl font-extrabold text-indigo-900 mb-4">
              The College Motto
            </h3>

            <p class="text-lg font-semibold text-indigo-800 italic mb-4">
              “To Be, Not To Seem”
            </p>

            <div class="space-y-4">
              <p class="text-gray-700 justified-text">
                The motto was carefully selected to reflect the founders’ desire to train students with strong values that guide them in life, rather than simply following what the world has to offer.
              </p>

              <p class="text-gray-700 justified-text">
                It emphasizes authenticity, integrity, and inner strength - encouraging learners to cultivate genuine character instead of pursuing outward appearances.
              </p>

              <div class="pt-4">
                <a
                  href="https://stmark.sc.ug/elearning/"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-900 to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-2xl hover:from-indigo-800 hover:to-indigo-600 transform hover:-translate-y-1 transition duration-300"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 7l5 5m0 0l-5 5m5-5H6"
                    />
                  </svg>
                  Visit eSpace
                </a>
              </div>
            </div>
          </div>

          <div class="group rounded-2xl p-8 bg-white border-2 border-indigo-200 shadow-lg hover:shadow-2xl transition duration-300">
            <h3 class="text-2xl font-extrabold text-indigo-900 mb-6 text-center">
              Core Values (GREET)
            </h3>

            <div class="space-y-5">
              <div class="flex items-start gap-4">
                <div class="value-circle">G</div>
                <div>
                  <p class="font-bold text-indigo-900">Godliness</p>
                  <p class="text-gray-600 text-sm">Nurturing spiritual and ethical grounding in every learner.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="value-circle">R</div>
                <div>
                  <p class="font-bold text-indigo-900">Reliability</p>
                  <p class="text-gray-600 text-sm">Dependable teaching, administration and pastoral care.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="value-circle">E</div>
                <div>
                  <p class="font-bold text-indigo-900">Ethics</p>
                  <p class="text-gray-600 text-sm">Integrity and moral responsibility in action and learning.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="value-circle">E</div>
                <div>
                  <p class="font-bold text-indigo-900">Excellence</p>
                  <p class="text-gray-600 text-sm">High academic and co-curricular standards, pursued consistently.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="value-circle">T</div>
                <div>
                  <p class="font-bold text-indigo-900">Team Work</p>
                  <p class="text-gray-600 text-sm">Collaboration across students, staff and the wider community.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="group rounded-2xl p-8 bg-white border-2 border-indigo-100 shadow-lg hover:shadow-2xl transition duration-300">
            <h3 class="text-2xl font-extrabold text-indigo-900 mb-4">
              Why Choose St. Mark’s?
            </h3>

            <p class="text-gray-700 justified-text mb-4">
              St. Mark’s College Namagoma promotes education and excellence with particular focus on each student - academically, spiritually and morally.
            </p>

            <p class="text-gray-700 justified-text mb-4">
              Students benefit from modern facilities and a serene learning environment. Our dedicated teachers nurture academic excellence and strong character. We prepare learners for leadership and responsible citizenship.
            </p>

            <div class="mt-6">
              <a
                href="/admissions"
                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-900 text-white font-semibold rounded-md shadow hover:bg-indigo-800 transition duration-300 group"
              >
                <svg class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </svg>
                Visit Admissions
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

  <!-- ================= MEDIA SHOWCASE ================= -->
<section 
  v-if="mediaToShow && mediaToShow.length" 
  class="pt-6 pb-8 bg-gray-50 w-full"
>

  <!-- FULL WIDTH (almost no padding) -->
  <div class="w-full space-y-6">

    <div 
      v-for="item in mediaToShow" 
      :key="item.id"
    >

      <!-- IMAGE (natural height) -->
      <img
        v-if="item.type === 'image'"
        :src="item.file_url"
        :alt="item.title"
        class="w-full object-cover rounded-md"
      />

      <!-- YOUTUBE -->
      <div 
        v-else-if="item.type === 'link' && item.video_url" 
        class="w-full aspect-video overflow-hidden rounded-md"
      >
        <iframe
          :src="getYouTubeEmbed(item.video_url)"
          class="w-full h-full"
          frameborder="0"
          allowfullscreen
        ></iframe>
      </div>

      <!-- VIDEO -->
      <video
        v-else-if="item.type === 'video'"
        :src="item.file_url"
        controls
        class="w-full object-contain bg-black rounded-md"
      ></video>

    </div>

  </div>
</section>

    <!-- ================= MISSION & VISION ================= -->
<section class="py-12 bg-gradient-to-b from-white to-indigo-50/40">
  <div class="container-wide">

    <!-- Title -->
  <div class="mb-2"></div>
    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">

      <!-- Mission -->
      <article class="mv-card-vertical">
        <div class="mv-top">
          <div class="mv-icon">🎯</div>
          <h4 class="mv-title">Our Mission</h4>
        </div>

        <p class="mv-text">
          To provide top quality secondary education that nurtures in our students a zest for life, a spirit of enterprise, community service, and leadership. The College aims at providing a balanced education curriculum to students so that they can be adaptable to the ever-changing domestic and global environment.
        </p>
      </article>

      <!-- Vision -->
      <article class="mv-card-vertical">
        <div class="mv-top">
          <div class="mv-icon alt">🌍</div>
          <h4 class="mv-title">Our Vision</h4>
        </div>

        <p class="mv-text">
          The College vision is to be a leading academic institution in Uganda and in the East African region producing highly successful and respected people in the different aspects of life.
        </p>
      </article>

    </div>
  </div>
</section>

    <!-- ================= RELATED POSTS ================= -->
    <section v-if="relatedPosts && relatedPosts.length" class="container-wide py-12">
      <h3 class="section-title text-2xl md:text-3xl font-extrabold text-indigo-900 mb-8">
        Articles You May Have Missed
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <article
          v-for="post in relatedPosts"
          :key="post.id"
          class="flex items-start gap-4 bg-white rounded-lg p-4 shadow-sm hover:shadow-lg transition"
        >
          <a :href="post.url || `/news/${post.slug || post.id}`" class="block flex-shrink-0">
            <img
              :src="post.image_url || placeholderNewsImage"
              :alt="post.title"
              class="w-28 h-20 md:w-32 md:h-24 object-cover rounded-md"
            />
          </a>

          <div class="flex-1">
            <a
              :href="post.url || `/news/${post.slug || post.id}`"
              class="text-gray-900 hover:text-indigo-700 font-semibold text-base md:text-lg leading-snug line-clamp-3"
            >
              {{ post.title }}
            </a>

            <p class="text-sm text-gray-600 mt-2 line-clamp-2">
              {{ post.excerpt || truncate(post.content || '', 110) }}
            </p>

            <div class="mt-3 text-sm text-gray-400">
              {{ formattedDate(post.date || post.created_at || post.published_at) }}
            </div>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { usePageView } from '@/composables/usePageView'

const assetBaseUrl = document.querySelector('meta[name="asset-base-url"]')?.content || '/'
const placeholderNewsImage = `${assetBaseUrl.replace(/\/$/, '')}/images/placeholder-news.jpg`

const props = defineProps({
  slides: { type: Array, default: () => [] },
  autoplayDelay: { type: Number, default: 10000 },
  newsItems: { type: Array, default: () => [] },
  mediaItems: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false }
})

// Track page view for home page
usePageView('home', null)

const computedSlides = computed(() =>
  (props.slides || [])
    .map(s => ({
      id: s.id,
      title: s.title || '',
      subtitle: s.caption || '',
      type: s.video_url ? 'video' : 'image',
      image_url: s.image_url || null,
      video_url: s.video_url || null,
      src: s.video_url || s.image_url || '',
      alt: s.title || '',
      effect: s.effect || 'zoomOut',
      is_active: s.is_active === undefined ? true : !!s.is_active
    }))
    .filter(s => s.src && s.is_active)
)

const featuredPosts = computed(() => {
  const all = (props.newsItems || []).filter(Boolean)

  const flagged = all.filter(i =>
    i.is_featured ||
    i.featured ||
    (i.tags && i.tags.includes && i.tags.includes('featured'))
  )

  if (flagged.length) return flagged.slice(0, 8)
  return all.slice(0, 8)
})

const featuredPostsLoop = computed(() => {
  const posts = featuredPosts.value
  if (posts.length < 2) return posts.map(p => ({ post: p, key: `${p.id}` }))
  return [
    ...posts.map(p => ({ post: p, key: `${p.id}-a` })),
    ...posts.map(p => ({ post: p, key: `${p.id}-b` }))
  ]
})

const featuredScrollEl = ref(null)
let featuredRAF = null
let featuredPaused = false
let featuredResumeTimer = null
const prefersReducedMotionMQ = typeof window !== 'undefined' && window.matchMedia
  ? window.matchMedia('(prefers-reduced-motion: reduce)')
  : null

function startFeaturedMotion() {
  if (prefersReducedMotionMQ && prefersReducedMotionMQ.matches) return
  if (featuredPosts.value.length < 2) return
  stopFeaturedMotion()
  const speed = 0.5
  const step = () => {
    const track = featuredScrollEl.value
    if (track && !featuredPaused) {
      track.scrollLeft += speed
      const halfWidth = track.scrollWidth / 2
      if (track.scrollLeft >= halfWidth) {
        track.scrollLeft -= halfWidth
      }
    }
    featuredRAF = requestAnimationFrame(step)
  }
  featuredRAF = requestAnimationFrame(step)
}

function stopFeaturedMotion() {
  if (featuredRAF) {
    cancelAnimationFrame(featuredRAF)
    featuredRAF = null
  }
}

function pauseFeatured() {
  featuredPaused = true
  if (featuredResumeTimer) {
    clearTimeout(featuredResumeTimer)
    featuredResumeTimer = null
  }
}
function resumeFeatured() {
  featuredPaused = false
}
function scheduleResumeFeatured() {
  if (featuredResumeTimer) clearTimeout(featuredResumeTimer)
  featuredResumeTimer = setTimeout(resumeFeatured, 1500)
}

function scrollFeatured(dir) {
  const el = featuredScrollEl.value
  if (!el) return
  pauseFeatured()
  const amount = Math.min(el.clientWidth * 0.8, 600)
  el.scrollBy({ left: dir * amount, behavior: 'smooth' })
  scheduleResumeFeatured()
}
function onFeaturedImgError(e) {
  e.target.src = '/images/placeholder-news.jpg'
}

const mediaToShow = computed(() => {
  return (props.mediaItems || [])
    .filter(item => item.type === 'image' || item.type === 'link' || item.type === 'video')
    .slice(0, 2)
})

const relatedPosts = computed(() => {
  const allPosts = props.newsItems || []

  return allPosts
    .slice(5)
    .slice(0, 12)
})
const current = ref(0)
let autoplayInterval = null
const autoplayDelayMs = computed(() => props.autoplayDelay)

function goTo(i) {
  current.value = i
  resetAutoplay()
}
function goNext() {
  if (!computedSlides.value.length) return
  current.value = (current.value + 1) % computedSlides.value.length
}
function goPrev() {
  if (!computedSlides.value.length) return
  current.value = (current.value - 1 + computedSlides.value.length) % computedSlides.value.length
}
function goNextAndReset() {
  goNext()
  resetAutoplay()
}
function goPrevAndReset() {
  goPrev()
  resetAutoplay()
}

function play() {
  stop()
  if (!computedSlides.value.length) return
  autoplayInterval = setInterval(() => {
    goNext()
  }, autoplayDelayMs.value)
}

function stop() {
  if (autoplayInterval) {
    clearInterval(autoplayInterval)
    autoplayInterval = null
  }
}
function pause() {
  stop()
}
function resetAutoplay() {
  play()
}

function onKey(e) {
  if (e.key === 'ArrowLeft') goPrevAndReset()
  if (e.key === 'ArrowRight') goNextAndReset()
}

let touchStartX = 0
function onTouchStart(e) {
  touchStartX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX
  pause()
}
function onTouchEnd(e) {
  const endX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX
  const dx = endX - touchStartX
  const threshold = 40
  if (dx > threshold) goPrevAndReset()
  else if (dx < -threshold) goNextAndReset()
  else resetAutoplay()
}

function animationClass(effect) {
  switch (effect) {
    case 'zoomIn': return 'anim-zoom-in'
    case 'zoomOut': return 'anim-zoom-out'
    case 'fadeSubtle': return 'anim-fade-subtle'
    case 'bounce': return 'anim-bounce'
    case 'wheel': return 'anim-wheel'
    case 'float': return 'anim-float'
    default: return 'anim-zoom-out'
  }
}

onMounted(async () => {
  play()
  window.addEventListener('keydown', onKey)
  await nextTick()
  startFeaturedMotion()
})

onBeforeUnmount(() => {
  stop()
  window.removeEventListener('keydown', onKey)
  stopFeaturedMotion()
  if (featuredResumeTimer) clearTimeout(featuredResumeTimer)
})

watch(() => computedSlides.value.length, (n) => {
  if (n && current.value >= n) current.value = 0
  resetAutoplay()
})

const backgroundExpanded = ref(false)

const backgroundFull = `
St. Mark’s College Namagoma is a high-quality private secondary school founded in 2003. It is a mixed boarding school offering both Arts and Sciences at “O” and “A” Level. The College is owned by experienced and well-educated individuals with a strong commitment to excellence in academics and post-school life. Their high academic achievements enable the Directors to fully understand students’ and school needs, and they are ready and capable of providing the best environment for effective learning.

The College has a seasoned Board of Governors comprising educationists, bankers, and senior managers in Ugandan society. The Head Teacher and the entire staff are carefully selected to meet the high standards of the College. The school is duly licensed and registered by the Ministry of Education and Sports. Its registration and UNEB centre numbers are PSS/S/261 and U1664 respectively.

Since opening its doors to students on 10th February 2003, the College has achieved tremendous success in student recruitment and retention. It now accommodates over 2,000 students in both “O” and “A” Level sections. The school boasts of high academic performance and sends many students to both public and private universities in Uganda.

LOCATION

The College campus sits on 30 acres of land at Namagoma, 10 miles along the Kampala–Masaka Road, just 1 km off the main road. It neighbors well-established and successful schools such as King’s College Budo, Trinity College Nabbingo, and St. Lawrence Colleges — often referred to as the “Massachusetts Corridor of Uganda.” This environment provides healthy academic competition and inspires high achievement.

The College is located away from the hustle and bustle of town, creating a quiet and conducive atmosphere for academic concentration, while still remaining easily accessible.

EDUCATION PHILOSOPHY

Our philosophy is to value all students according to their individual abilities and special talents. This approach enables students to grow into mature, compassionate, honest, and honorable individuals.

Specifically, our curriculum aims to develop individuals who:

• Are resourceful and responsible citizens.  
• Promote national unity and harmonious communities.  
• Display a high sense of discipline, ethical and spiritual values.  
• Take collective responsibility, love and care for others, and respect public property.  
• Possess skills for analyzing and solving problems, with a strong sense of innovation and positive attitudes toward work.

Our vision is to be a leading secondary school in Uganda and the East African region.

Programs at St. Mark’s College are stimulating, rewarding, and forward-looking. They focus on holistic development by embracing academic excellence, character formation, self-determination, leadership, life skills, and the development of individual talents. Students benefit from excellent facilities, a beautiful and serene environment, and an engaging curriculum delivered by a team of well-qualified and experienced staff who value every learner individually.

All interested parents and students from across East Africa are warmly welcome to become part of the St. Mark’s College fraternity.

Please take time to browse through our website. We are confident you will be impressed by the serene environment and the quality educational programs we offer.

We invite you to visit our campus and experience firsthand why St. Mark’s College Namagoma is the place to be. Our teachers, staff, students, and administration are always ready to support you in becoming the very best you can be.

Our motto says it all: “To Be, Not To Seem.”

We encourage you to contact the College for further information.
`.trim()

const smallPreview = computed(() => {
  const idx = backgroundFull.indexOf('\n\n')
  return idx === -1 ? backgroundFull : backgroundFull.slice(0, idx)
})

const maxIntroLength = 420
const backgroundIntro = computed(() => {
  if (backgroundFull.length <= maxIntroLength) return backgroundFull
  return backgroundFull.slice(0, maxIntroLength).trim() + '...'
})

function toggleBackground() {
  backgroundExpanded.value = !backgroundExpanded.value
}

const itemsPerPage = ref(6)
const shownCount = ref(itemsPerPage.value)

const newsItemsShown = computed(() => {
  return (props.newsItems || []).slice(0, shownCount.value)
})

function beautifulDate(value) {
  if (!value) return ''
  try {
    const d = new Date(value)
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  } catch {
    return value
  }
}

function truncate(str, max = 120) {
  if (!str) return ''
  return str.length > max ? str.slice(0, max).trim() + '...' : str
}

function formattedDate(dateStr) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleString(undefined, { year: 'numeric', month: 'short', day: '2-digit' })
  } catch {
    return dateStr
  }
}

function getYouTubeEmbed(url) {
  if (!url) return ''

  let videoId = null

  if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1]?.split('?')[0]
  } else if (url.includes('youtube.com/watch')) {
    const params = new URL(url).searchParams
    videoId = params.get('v')
  } else if (url.includes('youtube.com/embed/')) {
    videoId = url.split('embed/')[1]?.split('?')[0]
  }

  if (!videoId) return ''
  return `https://www.youtube.com/embed/${videoId}`
}

function dateParts(dateStr) {
  if (!dateStr) return { day: '', month: '' }
  try {
    const d = new Date(dateStr)
    if (isNaN(d)) return { day: '', month: '' }
    const day = String(d.getDate()).padStart(2, '0')
    const month = d.toLocaleString(undefined, { month: 'short' }).toUpperCase()
    return { day, month }
  } catch {
    return { day: '', month: '' }
  }
}

function cardBackgroundStyle(item) {
  const img = item.image_url || placeholderNewsImage
  return {
    backgroundImage: `url('${img}')`,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    position: 'relative'
  }
}

function isImage(item) {
  if (!item || !item.file_url) return false
  return item.type === 'image' || /\.(jpe?g|png|webp|gif|svg)(\?.*)?$/i.test(item.file_url)
}

function isDirectVideo(item) {
  if (!item || !item.file_url) return false
  return item.type === 'video' && /\.(mp4|webm|ogg)(\?.*)?$/i.test(item.file_url)
}

function isYouTube(item) {
  if (!item || !item.file_url) return false
  return /youtube\.com|youtu\.be/.test(item.file_url)
}

function isVimeo(item) {
  if (!item || !item.file_url) return false
  return /vimeo\.com/.test(item.file_url)
}

function getVimeoEmbed(url = '') {
  const m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/)
  const id = m ? m[1] : null
  if (!id) return url
  return `https://player.vimeo.com/video/${id}`
}
</script>

<script>
export default { layout: MainLayout }
</script>

<style scoped>
.container-wide {
  width: 100%;
  max-width: 1600px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 2rem;
  padding-right: 2rem;
}

@media (min-width: 1536px) {
  .container-wide {
    max-width: 1800px;
    padding-left: 2.5rem;
    padding-right: 2.5rem;
  }
}

@media (min-width: 1920px) {
  .container-wide {
    max-width: none;
    width: 96%;
    padding-left: 0;
    padding-right: 0;
  }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .will-change-transform,
  .transition-transform,
  .transition-all,
  .anim-zoom-in,
  .anim-zoom-out,
  .anim-fade-subtle,
  .anim-bounce,
  .anim-wheel,
  .anim-float {
    animation: none !important;
    transition: none !important;
    transform: none !important;
  }
}

.related-thumb {
  border-radius: 0.375rem;
  object-fit: cover;
}

@media (min-width: 768px) {
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

.inactive-img {
  transform: scale(1) translateZ(0);
  opacity: 0.9;
}

.anim-zoom-in {
  transform: scale(1.14) translateZ(0);
  transition: transform 900ms cubic-bezier(.2,.9,.2,1);
}
.anim-zoom-out {
  transform: scale(1) translateZ(0);
  animation: zoomOutKenburns 10s ease-in-out both;
}
@keyframes zoomOutKenburns {
  0% { transform: scale(1.12) }
  50% { transform: scale(1.06) }
  100% { transform: scale(1.12) }
}
.anim-fade-subtle {
  animation: fadeSubtle 10s ease-in-out both;
}
@keyframes fadeSubtle {
  0% { transform: scale(1.02); opacity: 0.95 }
  100% { transform: scale(1.02); opacity: 0.95 }
}
.anim-bounce {
  animation: bounceSlow 10s cubic-bezier(.2,.6,.2,1) both;
}
@keyframes bounceSlow {
  0% { transform: translateY(0) }
  25% { transform: translateY(-6px) }
  50% { transform: translateY(0) }
  75% { transform: translateY(-3px) }
  100% { transform: translateY(0) }
}
.anim-wheel {
  transform-origin: center;
  animation: wheelRotate 10s ease-in-out both;
}
@keyframes wheelRotate {
  0% { transform: rotateY(-12deg) }
  50% { transform: rotateY(12deg) }
  100% { transform: rotateY(-12deg) }
}
.anim-float {
  animation: floatSlow 12s ease-in-out both;
}
@keyframes floatSlow {
  0% { transform: translateY(0) }
  50% { transform: translateY(-8px) }
  100% { transform: translateY(0) }
}

.decorative-panel {
  position: relative;
  padding: 2px;
  border-radius: 1.75rem;
  background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,250,252,0.9));
  box-shadow: 0 30px 80px rgba(2,6,23,0.12),
              0 15px 40px rgba(2,6,23,0.08);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(20px);
}
.decorative-panel:hover {
  box-shadow: 0 40px 100px rgba(2,6,23,0.18),
              0 20px 50px rgba(2,6,23,0.12);
  transform: translateY(-4px);
}
.decorative-panel::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 1.75rem;
  padding: 4px;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  background: linear-gradient(135deg, rgba(59,130,246,0.25), rgba(99,102,241,0.2), rgba(250,204,21,0.15));
  box-shadow: 0 20px 50px rgba(2,6,23,0.1);
}
.decorative-frame {
  box-shadow: 0 20px 50px rgba(2,6,23,0.15), inset 0 0 0 5px rgba(255,255,255,0.05);
  border-radius: 20px;
  background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0));
  border: 2px solid rgba(99,102,241,0.15);
  transition: all 0.5s ease;
}
.inner-accent {
  border-radius: 18px;
  border: 4px solid rgba(59,130,246,0.18);
  mix-blend-mode: screen;
  pointer-events: none;
  box-shadow: 0 12px 30px rgba(59,130,246,0.15);
}

section [role="article"] {
  border-radius: 1.25rem;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
section [role="article"]:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 25px 60px rgba(2,6,23,0.15);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 1023px) {
  .group img {
    height: 240px;
    object-position: center;
  }
  .left-image {
    height: 240px;
  }
}

.site-heading,
.welcome-heading,
.section-title {
  font-family: "Merriweather", Georgia, "Times New Roman", serif;
  color: #062347;
  text-rendering: optimizeLegibility;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.justified-text {
  text-align: justify;
  line-height: 1.8;
  letter-spacing: 0.01em;
}

.news-heading {
  font-family: "Merriweather", Georgia, serif;
  font-weight: 700;
  font-size: 1.25rem;
  line-height: 1.35;
  color: #ffffff;
  margin: 0;
  text-shadow:
    0 4px 12px rgba(0, 0, 0, 0.85),
    0 2px 6px rgba(0, 0, 0, 0.75);
  letter-spacing: 0.15px;
  text-align: left;
}

.news-content {
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.4;
  color: #ffffff;
  text-shadow: 0 4px 12px rgba(0,0,0,0.85);
  opacity: 1;
  letter-spacing: 0.2px;
}

.site-heading {
  font-family: "Merriweather", Georgia, "Times New Roman", serif;
  font-weight: 900;
  font-size: 2.5rem;
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: #ffffff;
  text-shadow:
    0 8px 32px rgba(0, 0, 0, 0.9),
    0 4px 16px rgba(0, 0, 0, 0.8),
    0 2px 8px rgba(0, 0, 0, 0.7);
  -webkit-text-stroke: 0.3px rgba(0,0,0,0.4);
}

@media (min-width: 768px) {
  .site-heading {
    font-size: 3.25rem;
  }
}
@media (min-width: 1024px) {
  .site-heading {
    font-size: 3.75rem;
  }
}
@media (min-width: 1536px) {
  .site-heading {
    font-size: 4.25rem;
  }
  .welcome-heading {
    font-size: 2.5rem;
  }
  .section-title {
    font-size: 2.25rem;
  }
}

.welcome-heading {
  font-weight: 800;
  font-size: 1.6rem;
  color: #022944;
  margin-bottom: 0.75rem;
  background: linear-gradient(135deg, #022944 0%, #1e40af 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 0.3px;
}

@media (min-width: 768px) {
  .welcome-heading {
    font-size: 1.875rem;
  }
}
@media (min-width: 1024px) {
  .welcome-heading {
    font-size: 2.25rem;
  }
}

.section-title {
  font-family: "Merriweather", Georgia, serif;
  color: #071a33;
}

.hero-cta {
  background: linear-gradient(135deg, #0f4ac5 0%, #1e40af 50%, #0b3a9a 100%);
  box-shadow: 0 12px 35px rgba(12, 42, 120, 0.4),
              0 6px 20px rgba(12, 42, 120, 0.3);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.15);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.hero-cta:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 18px 45px rgba(12, 42, 120, 0.5),
              0 10px 25px rgba(12, 42, 120, 0.4);
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0f4ac5 100%);
}

.readmore-btn {
  background: linear-gradient(135deg, #0b3a9a 0%, #1e40af 50%, #062347 100%);
  color: #fff;
  border: 2px solid rgba(255,255,255,0.1);
  box-shadow: 0 8px 25px rgba(2,6,23,0.2);
  font-weight: 700;
  letter-spacing: 0.5px;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.readmore-btn:hover {
  filter: brightness(1.1);
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 12px 35px rgba(2,6,23,0.3);
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0b3a9a 100%);
}

.news-date-badge {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.news-date-badge .day {
  line-height: 1;
}

.motto-section {
  background: linear-gradient(135deg, rgba(247,250,255,0.95) 0%, rgba(255,251,235,0.9) 100%);
  border-top: 2px solid rgba(99,102,241,0.1);
  backdrop-filter: blur(10px);
}

.motto-section .w-10 {
  box-shadow: 0 6px 18px rgba(2,6,23,0.06);
}

.value-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #312e81 100%);
  color: #ffffff;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 25px rgba(30, 58, 138, 0.35),
              0 5px 15px rgba(30, 58, 138, 0.25);
  flex-shrink: 0;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.value-circle:hover {
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 15px 35px rgba(30, 58, 138, 0.45),
              0 8px 20px rgba(30, 58, 138, 0.35);
}

.featured-wrapper {
  position: relative;
  background: linear-gradient(135deg, rgba(248,250,252,0.95) 0%, rgba(238,242,255,0.9) 100%);
  border-radius: 1.5rem;
  padding: 3rem;
  border: 2px solid rgba(79, 70, 229, 0.12);
  box-shadow:
    0 30px 70px rgba(15, 23, 42, 0.12),
    0 15px 35px rgba(15, 23, 42, 0.08);
  overflow: hidden;
  backdrop-filter: blur(15px);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.featured-wrapper::before {
  content: "";
  position: absolute;
  top: -150px;
  right: -150px;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%);
  z-index: 0;
  animation: gradientPulse 8s ease-in-out infinite;
}
@keyframes gradientPulse {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}
.featured-wrapper > * {
  position: relative;
  z-index: 1;
}
.featured-nav-btn {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  color: #1e3a8a;
  border: 1px solid rgba(30,58,138,0.15);
  box-shadow: 0 8px 20px rgba(2,6,23,0.1);
  transition: all 0.3s ease;
}
.featured-nav-btn:hover {
  background: #1e3a8a;
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(30,58,138,0.3);
}

.featured-track {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  scroll-snap-type: none;
  padding: 0.25rem 0.25rem 0.75rem;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: rgba(79,70,229,0.35) transparent;
}
.featured-track::-webkit-scrollbar {
  height: 8px;
}
.featured-track::-webkit-scrollbar-thumb {
  background: rgba(79,70,229,0.3);
  border-radius: 999px;
}
.featured-track::-webkit-scrollbar-track {
  background: transparent;
}

.featured-card {
  flex: 0 0 auto;
  width: 250px;
  scroll-snap-align: start;
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(2,6,23,0.08);
  border: 1px solid rgba(79,70,229,0.08);
  transition: transform 0.35s cubic-bezier(0.4,0,0.2,1), box-shadow 0.35s cubic-bezier(0.4,0,0.2,1);
}
@media (min-width: 640px) {
  .featured-card { width: 270px; }
}
@media (min-width: 1024px) {
  .featured-card { width: 288px; }
}
.featured-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 45px rgba(2,6,23,0.16);
}

.featured-card-img {
  position: relative;
  height: 160px;
  overflow: hidden;
  flex-shrink: 0;
}
.featured-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4,0,0.2,1);
}
.featured-card:hover .featured-card-img img {
  transform: scale(1.08);
}
.featured-date-badge {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  background: rgba(6,35,71,0.75);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.4px;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  backdrop-filter: blur(4px);
}

.featured-card-body {
  padding: 1rem 1.1rem 1.25rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.featured-excerpt {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.5;
}
.featured-readmore {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: auto;
  padding-top: 0.85rem;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.3px;
  color: #1e40af;
  text-transform: uppercase;
  transition: gap 0.3s ease, color 0.3s ease;
}
.featured-card:hover .featured-readmore {
  gap: 0.6rem;
  color: #0b3a9a;
}

@media (prefers-reduced-motion: reduce) {
  .featured-track {
    scroll-behavior: auto;
  }
  .featured-card,
  .featured-card-img img,
  .featured-readmore {
    transition: none !important;
  }
}

.leadership-section {
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  position: relative;
}
.leadership-divider {
  width: 80px;
  height: 4px;
  margin: 16px auto 0;
  border-radius: 4px;
  background: linear-gradient(90deg, #1e3a8a, #facc15);
}
.leadership-card {
  position: relative;
  background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.9) 100%);
  border-radius: 1.75rem;
  padding-top: 105px;
  text-align: center;
  border: 2px solid rgba(30,58,138,0.12);
  box-shadow:
    0 25px 60px rgba(2,6,23,0.1),
    0 10px 25px rgba(2,6,23,0.06);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  transform: scale(0.3) translateY(40px);
  backdrop-filter: blur(10px);
  animation: zoomInEntrance 0.8s cubic-bezier(.34,1.56,.64,1) forwards,
             continuousBounce 3s ease-in-out infinite 0.8s;
}
.leadership-card:hover {
  transform: translateY(-15px) scale(1.03);
  box-shadow:
    0 45px 90px rgba(2,6,23,0.18),
    0 20px 40px rgba(2,6,23,0.12);
  border-color: rgba(30,58,138,0.25);
  animation-play-state: paused;
  backdrop-filter: blur(15px);
}
.leadership-card:nth-child(1) { animation-delay: 0.2s; }
.leadership-card:nth-child(2) { animation-delay: 0.4s; }
.leadership-card:nth-child(3) { animation-delay: 0.6s; }
@keyframes zoomInEntrance {
  0% {
    opacity: 0;
    transform: scale(0.3) translateY(40px);
  }
  60% {
    opacity: 1;
    transform: scale(1.05) translateY(-8px);
  }
  80% {
    transform: scale(0.98) translateY(4px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
@keyframes continuousBounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}
.leadership-avatar {
  position: absolute;
  top: -85px;
  left: 50%;
  transform: translateX(-50%);
  width: 155px;
  height: 155px;
  border-radius: 50%;
  padding: 8px;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 40%, #facc15 100%);
  box-shadow: 0 20px 45px rgba(30,58,138,0.35),
              0 10px 25px rgba(30,58,138,0.25);
  animation: avatarPulse 2.5s ease-in-out infinite 1.5s;
  backdrop-filter: blur(5px);
}
@keyframes avatarPulse {
  0%, 100% {
    box-shadow: 0 15px 35px rgba(30,58,138,0.3);
  }
  50% {
    box-shadow: 0 20px 45px rgba(30,58,138,0.45);
  }
}
.leadership-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  border: 6px solid #ffffff;
  transition: transform 0.4s ease;
}
.leadership-card:hover .leadership-avatar img {
  transform: scale(1.05);
}
.leadership-content {
  padding: 2.75rem 2rem 3.25rem;
  background: linear-gradient(180deg, rgba(255,255,255,0.6) 0%, rgba(248,250,252,0.85) 100%);
  border-radius: 0 0 1.75rem 1.75rem;
  backdrop-filter: blur(5px);
}
.leadership-name {
  font-family: "Merriweather", Georgia, serif;
  font-weight: 900;
  font-size: 1.35rem;
  color: #062347;
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.leadership-role {
  margin-top: 8px;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #3b82f6;
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.leadership-text {
  margin-top: 1.25rem;
  font-size: 0.98rem;
  color: #4b5563;
  line-height: 1.7;
  font-weight: 500;
}
.leadership-btn,
.leadership-btn-alt {
  display: inline-block;
  margin-top: 1.5rem;
  padding: 0.75rem 2rem;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  text-transform: uppercase;
}
.leadership-btn {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: #fff;
  box-shadow: 0 8px 25px rgba(30,58,138,0.35);
  border: 2px solid transparent;
}
.leadership-btn:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 12px 35px rgba(30,58,138,0.45);
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}
.leadership-btn-alt {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: #fff;
  box-shadow: 0 8px 25px rgba(37,99,235,0.35);
  border: 2px solid transparent;
}
.leadership-btn-alt:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 12px 35px rgba(37,99,235,0.45);
  background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
}

.featured-title {
  font-family: "Merriweather", Georgia, serif;
  font-weight: 900;
  font-size: 1rem;
  color: #062347;
  line-height: 1.35;
  letter-spacing: 0.2px;
  transition: color 0.3s ease;
}
.featured-card:hover .featured-title {
  color: #0b3a9a;
}

@media (prefers-reduced-motion: reduce) {
  .leadership-card {
    animation: none !important;
    opacity: 1 !important;
    transform: none !important;
  }
  .leadership-avatar {
    animation: none !important;
  }
}


/* Card */
.mv-card-vertical {
  background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,250,252,0.9));
  border-radius: 1.75rem;
  padding: 2.25rem;
  border: 2px solid rgba(79, 70, 229, 0.15);
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12),
              0 12px 30px rgba(15, 23, 42, 0.08);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
  backdrop-filter: blur(10px);
}

.mv-card-vertical:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: 0 35px 80px rgba(15, 23, 42, 0.18),
              0 18px 40px rgba(15, 23, 42, 0.12);
  border-color: rgba(79, 70, 229, 0.25);
}

/* Top section */
.mv-top {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

/* Icon */
.mv-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #4f46e5 100%);
  color: white;
  box-shadow: 0 15px 35px rgba(30, 58, 138, 0.35),
              0 8px 20px rgba(30, 58, 138, 0.25);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.mv-icon:hover {
  transform: scale(1.1) rotate(-5deg);
  box-shadow: 0 20px 45px rgba(30, 58, 138, 0.45),
              0 12px 25px rgba(30, 58, 138, 0.35);
}

.mv-icon.alt {
  background: linear-gradient(135deg, #0f766e 0%, #2563eb 50%, #0d9488 100%);
}

/* Title */
.mv-title {
  font-family: "Merriweather", Georgia, serif;
  font-weight: 900;
  font-size: 1.5rem;
  color: #062347;
  letter-spacing: 0.2px;
  line-height: 1.3;
}

/* Beautiful text */
.mv-text {
  text-align: justify;
  font-size: 1.05rem;
  line-height: 1.9;
  color: #374151;
  font-weight: 500;
  letter-spacing: 0.15px;

  /* makes paragraphs look premium */
  text-rendering: optimizeLegibility;
}

/* Bigger screens enhancement */
@media (min-width: 1280px) {
  .mv-card-vertical {
    padding: 2.5rem;
  }

  .mv-title {
    font-size: 1.7rem;
  }

  .mv-text {
    font-size: 1.1rem;
    line-height: 2;
  }
}
.justified-title {
  text-align: left;
  line-height: 1.35;
  word-break: break-word;
}

</style>