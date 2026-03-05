<script setup lang="ts">
import { onMounted } from 'vue'
import { useManagerVerbStore } from '@/infrastructure/store/managerVerb.store'
import AddVerb from '@/ui/views/manager/AddVerb.vue'
import EditVerb from '@/ui/views/manager/EditVerb.vue'
import FilterVerb from '@/ui/views/manager/FilterVerb.vue'
import SpecialCharBox from '@/ui/components/SpecialCharBox.vue'

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

