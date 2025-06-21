<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useAlertStore } from '@/stores';

const alertStore = useAlertStore();
const { alert } = storeToRefs(alertStore);
</script>

<template>
  <div v-if="alert" class="container">
    <div class="m-3">
      <div class="alert alert-dismissable" :class="alert.type">
        <button @click="alertStore.clear()" class="button button-secondary close">×</button>
        <p>{{alert.message}}</p>
      </div>
    </div>
  </div>
</template>