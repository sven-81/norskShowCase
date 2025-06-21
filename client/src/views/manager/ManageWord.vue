<script setup lang="ts">
import { onMounted } from 'vue'
import { useManagerWordStore } from '@/stores'
import { AddWord, EditWord, FilterWord, SpecialCharBox } from '@/views/manager'

const wordsStore = useManagerWordStore()

onMounted(() => {
  wordsStore.getAll()
})
</script>

<template>
  <h1>Wörter verwalten</h1>
  <SpecialCharBox />
  <AddWord />
  <FilterWord />
  <EditWord />
</template>
