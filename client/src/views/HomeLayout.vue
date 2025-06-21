<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
</script>

<template>
  <h1 v-if="user">Jeg lærer norsk, {{user.firstName}}</h1>
  <h1 v-else>Jeg lærer norsk</h1>
  <p class="home">
    Willkommen zum Vokabeltrainer für Norwegisch!<br />
    Hier kannst du Wörter und Verben üben.<br /><br />
    Jeg ønsker deg suksess!
  </p>
</template>
