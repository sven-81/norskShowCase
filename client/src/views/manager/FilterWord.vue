<script setup lang="ts">
import { computed } from 'vue'
import { Field } from 'vee-validate'
import { useManagerWordStore } from '@/stores'
import { replaceSpecialChars } from '@/components/specialChars'

const wordsStore = useManagerWordStore()

const searchGerman = computed({
  get: () => wordsStore.searchGerman,
  set: (val) => (wordsStore.searchGerman = val)
})
const searchNorsk = computed({
  get: () => wordsStore.searchNorsk,
  set: (val) => {
    const converted = replaceSpecialChars(val)
    wordsStore.searchNorsk = converted
  }
})
</script>

<template>
  <h2>Wörter suchen</h2>
  <div id="filter-words">
    <Field
      type="text"
      name="german-search"
      v-model="searchGerman"
      placeholder="Deutsch"
      class="form-control"
      @keyup.enter="wordsStore.updateSearchTerm(searchGerman, 'DE')"
    />

    <Field
      type="text"
      name="norsk-search"
      id="filter-norsk"
      v-model="searchNorsk"
      placeholder="Norwegisch"
      class="form-control"
      @keyup.enter="wordsStore.updateSearchTerm(searchNorsk, 'NO')"
    />

    <button @click="wordsStore.updateSearchTerm('', 'none')" class="button">Filter aufheben</button>
  </div>
</template>
