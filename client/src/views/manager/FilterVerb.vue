<script setup lang="ts">
import { computed } from 'vue'
import { Field } from 'vee-validate'
import { useManagerVerbStore } from '@/stores'
import { replaceSpecialChars } from '@/components/specialChars'

const verbsStore = useManagerVerbStore()

const searchGerman = computed({
  get: () => verbsStore.searchGerman,
  set: (val) => (verbsStore.searchGerman = val)
})
const searchNorsk = computed({
  get: () => verbsStore.searchNorsk,
  set: (val) => {
    const converted = replaceSpecialChars(val)
    verbsStore.searchNorsk = converted
  }
})
</script>

<template>
  <h2>Verben suchen</h2>
  <div id="filter-words">
    <Field
      type="text"
      name="german-search"
      v-model="searchGerman"
      placeholder="Deutsch"
      class="form-control"
      @keyup.enter="verbsStore.updateSearchTerm(searchGerman, 'DE')"
    />

    <Field
      type="text"
      name="norsk-search"
      id="filter-norsk"
      v-model="searchNorsk"
      placeholder="Norwegisch"
      class="form-control"
      @keyup.enter="verbsStore.updateSearchTerm(searchNorsk, 'NO')"
    />

    <button @click="verbsStore.updateSearchTerm('', 'none')" class="button">Filter aufheben</button>
  </div>
</template>
