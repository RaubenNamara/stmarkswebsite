<script setup lang="ts">
import Editor from '@tinymce/tinymce-vue'

// Self-hosted (no cloud API key / no CDN call) - the one editor for every rich-content field
// (News, Posts, Campus Voices), replacing the old app's three inconsistent WYSIWYGs
// (CKEditor5/TinyMCE/Quill) per the plan's Phase 5 standardization.
import 'tinymce/tinymce'
import 'tinymce/icons/default'
import 'tinymce/themes/silver'
import 'tinymce/models/dom'
import 'tinymce/skins/ui/oxide/skin.css'
import 'tinymce/skins/content/default/content.css'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/image'
import 'tinymce/plugins/table'
import 'tinymce/plugins/code'
import 'tinymce/plugins/autolink'

const model = defineModel<string>({ default: '' })
</script>

<template>
  <Editor
    v-model="model"
    :init="{
      license_key: 'gpl',
      height: 320,
      menubar: false,
      plugins: 'link lists image table code autolink',
      toolbar:
        'undo redo | blocks | bold italic underline | bullist numlist | link image table | code',
      skin: false,
      content_css: false,
      promotion: false,
      branding: false,
    }"
  />
</template>
