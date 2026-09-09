<script setup lang="ts">
import { reactive } from 'vue'
import { useHead } from '@unhead/vue'
import { api } from '../../services/api'
import PageHeader from '../../components/PageHeader.vue'
import EmptyState from '../../components/EmptyState.vue'
import AvatarImage from '../../components/AvatarImage.vue'
import { staticAsset } from '../../utils/staticAsset'

useHead({ title: 'SMOSA Alumni' })

const { data } = await api.get('/smosa-alumni')
const items = data.data.smosa as Array<Record<string, any>>

function stripHtml(content: string | null): string | null {
  return content ? content.replace(/<[^>]*>/g, '') : null
}
function excerptFor(content: string | null): string | null {
  const text = stripHtml(content)
  if (!text) return null
  return text.length > 220 ? text.slice(0, 220) + '...' : text
}

const expanded = reactive<Record<number, boolean>>({})
</script>

<template>
  <PageHeader
    title="SMOSA Alumni"
    subtitle="Proud products of St Mark's College Namagoma - carrying its values into their careers and communities."
    :bg-image="staticAsset('storage/images/smacon.jpg')"
  />

  <section class="container-wide py-14">
    <EmptyState v-if="!items.length" message="No alumni stories published yet." />

    <template v-else>
      <div v-reveal class="mx-auto max-w-2xl text-center">
        <span class="eyebrow">Alumni Spotlight</span>
        <h2 class="mt-2 font-display text-2xl font-bold text-brand-navy sm:text-3xl">Where SMACON has taken them</h2>
        <p class="mt-3 text-gray-600">Old students of St Mark's College Namagoma, sharing how their journey shaped who they are today.</p>
      </div>

      <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="item in items" :key="item.id" v-reveal class="card card-interactive text-center">
          <AvatarImage :src="item.photo_url" :alt="item.name" size="lg" />
          <h3 class="mt-4 font-display text-base font-bold text-gray-900">{{ item.name }}</h3>
          <span v-if="item.profession" class="mt-2 inline-block rounded-full bg-brand-gold/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-navy">{{ item.profession }}</span>
          <p class="mt-4 whitespace-pre-line text-left text-sm leading-relaxed text-gray-600">
            {{ expanded[item.id] ? stripHtml(item.message) : excerptFor(item.message) }}
          </p>
          <button
            v-if="stripHtml(item.message) && stripHtml(item.message)!.length > (excerptFor(item.message)?.length ?? 0)"
            type="button"
            class="mt-2 text-sm font-semibold text-brand-navy hover:underline"
            @click="expanded[item.id] = !expanded[item.id]"
          >
            {{ expanded[item.id] ? 'View less' : 'View more' }}
          </button>
        </div>
      </div>
    </template>
  </section>
</template>
