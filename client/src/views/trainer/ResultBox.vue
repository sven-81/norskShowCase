<script setup lang="ts">
import {storeToRefs} from 'pinia'
import {watch} from 'vue'
import {useResultStore} from "@/stores";

const resultStore = useResultStore()
const {result} = storeToRefs(resultStore)

watch(result, () => {
})

</script>

<template>
  <div v-if="result.type.includes('correct')" id="correct" :class="result.type">{{ result.message }}</div>
  <div v-else-if="result.type.includes('mistake')" id="mistake" :class="result.type">
    <ul v-html="result.message"></ul>
  </div>
</template>
