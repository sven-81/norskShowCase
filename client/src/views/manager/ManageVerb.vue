<script setup>
import { onMounted } from 'vue'
import { useManagerVerbStore } from '@/stores'
import { AddVerb, EditVerb, FilterVerb, SpecialCharBox } from '@/views/manager'

const verbsStore = useManagerVerbStore()

onMounted(() => {
  verbsStore.getAll()
})
</script>

<template>
  <h1>Verben verwalten</h1>
  <SpecialCharBox />
  <AddVerb />
  <FilterVerb />
  <EditVerb />
</template>
