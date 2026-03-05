<script setup lang="ts">
import { onMounted } from 'vue'
import { useManagerWordStore } from '@/infrastructure/store/managerWord.store'
import AddWord from '@/ui/views/manager/AddWord.vue'
import EditWord from '@/ui/views/manager/EditWord.vue'
import FilterWord from '@/ui/views/manager/FilterWord.vue'
import SpecialCharBox from '@/ui/components/SpecialCharBox.vue'

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

