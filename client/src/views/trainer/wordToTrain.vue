<script setup lang="ts">
import { useTrainerWordStore } from '@/stores/trainerWord.store'

const activeWord = useTrainerWordStore()
activeWord.random()
</script>

<template>
  <div id="word-to-train">
    <div v-if="activeWord.loading">
      <div class="spinner"></div>
      <p>ich lade<br />jeg inviterer</p>
    </div>
    <p v-if="activeWord.word">{{ activeWord.word.german }}</p>
    <div v-if="activeWord.errorMessage">⚠️ {{ activeWord.errorMessage }}</div>
  </div>
</template>
