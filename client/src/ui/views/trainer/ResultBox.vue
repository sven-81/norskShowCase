<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { watch } from 'vue'
import { useResultStore } from '@/infrastructure/store/trainingResult.store'

const resultStore = useResultStore()
const { resultMessage, type } = storeToRefs(resultStore)

watch(type, () => {})
</script>

<template>
  <div v-if="type === 'correct'" id="correct" :class="type">{{ resultMessage }}</div>
  <div v-else-if="type === 'mistake'" id="mistake" :class="type">
    <ul v-html="resultMessage"></ul>
  </div>
</template>

