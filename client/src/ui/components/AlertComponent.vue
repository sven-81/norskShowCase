<script setup lang="ts">
import { useAlertStore } from '@/infrastructure/store/alert.store'

const alertStore = useAlertStore()
</script>

<template>
  <div v-if="alertStore.alert" class="container">
    <div class="m-3">
      <div class="alert alert-dismissable" :class="alertStore.alert.type">
        <button @click="alertStore.clear()" class="button button-secondary close">×</button>
        <p>{{ alertStore.alert.message }}</p>
      </div>
    </div>
  </div>
</template>

