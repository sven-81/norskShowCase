<script setup lang="ts">
import { useManagerWordStore } from '@/stores'
import { computed, ref } from 'vue'
import { replaceSpecialChars } from '@/components/specialChars'

const oldWord = ref<Record<number, { german: string; norsk: string }>>({})
const wordsStore = useManagerWordStore()

const computedFilteredWords = computed(() => wordsStore.computedFilteredWords)

function cacheOriginal(word) {
  if (!oldWord.value[word.id]) {
    oldWord.value[word.id] = {
      german: word.german,
      norsk: word.norsk
    }
  }
}

async function doneEdit(word) {
  function deleteOldWordToSaveMemory() {
    delete oldWord.value[word.id]
  }

  const original = oldWord.value[word.id] ?? null
  if (!original) return

  const isChanged = word.german !== original.german || word.norsk !== original.norsk

  if (!isChanged) {
    deleteOldWordToSaveMemory()
    return
  }
  try {
    if (isChanged && word.german && word.norsk) {
      await wordsStore.update(word)
    }
  } catch (error) {
    wordsStore.error = error.message

    word.german = original.german
    word.norsk = original.norsk
  }

  deleteOldWordToSaveMemory()
}

function onNorskInput(word, event) {
  const rawValue = event.target.value
  const trimmedValue = rawValue.trim()
  const convertedValue = replaceSpecialChars(trimmedValue)
  word.norsk = convertedValue
}

function handleFocus(word) {
  wordsStore.clearError()
  cacheOriginal(word)
}
</script>

<template>
  <h2>Wörter bearbeiten</h2>
  <div id="edit-words">
    <div class="table">
      <div class="header-id">Id</div>
      <div class="header">Deutsch</div>
      <div class="header">Norwegisch</div>
      <div class="header-button"></div>

      <template v-if="computedFilteredWords.length">
        <template v-for="word in computedFilteredWords" :key="word.id">
          <div class="cell">{{ word.id }}</div>
          <div class="cell">
            <input class="form-control" v-model.trim="word.german" @focus="handleFocus(word)" />
          </div>
          <div class="cell">
            <input
              class="form-control"
              :value="word.norsk"
              @input="(event) => onNorskInput(word, event)"
              @focus="handleFocus(word)"
            />
          </div>
          <div class="buttons">
            <div class="cell">
              <button @click="doneEdit(word)" class="button button-primary">speichern</button>
            </div>
            <div class="cell">
              <button
                @click="wordsStore.delete(word.id)"
                class="button-delete"
                :disabled="word.isDeleting"
              >
                löschen
              </button>
            </div>
          </div>
        </template>
      </template>
    </div>

    <div v-if="wordsStore.loading">
      <div class="spinner"></div>
    </div>

    <div v-if="wordsStore.error">
      <div class="text-danger">
        <p>{{ wordsStore.error }}</p>
      </div>
    </div>
  </div>
</template>
