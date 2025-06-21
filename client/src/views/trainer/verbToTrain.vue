<script setup lang="ts">
import { useTrainerVerbStore } from '@/stores/trainerVerb.store'

const activeVerb = useTrainerVerbStore()
activeVerb.random()
</script>

<template>
  <div id="word-to-train">
    <div v-if="activeVerb.loading">
      <div class="spinner"></div>
      <p>ich lade<br />jeg inviterer</p>
    </div>
    <p v-if="activeVerb.verb">{{ activeVerb.german }}</p>
    <div v-if="activeVerb.errorMessage">⚠️ {{ activeVerb.errorMessage }}</div>
  </div>
</template>
