<script setup lang="ts">
import { useManagerWordStore } from '@/infrastructure/store/managerWord.store'
import { computed, ref } from 'vue'
import { replaceSpecialChars } from '@/domain/service/SpecialCharReplacer'

const oldWord = ref<Record<number, { german: string; norsk: string }>>({})
const wordsStore = useManagerWordStore()

const computedFilteredWords = computed(() => wordsStore.computedFilteredWords)

function cacheOriginal(word: any): void {
    if (!oldWord.value[word.id]) {
        oldWord.value[word.id] = {
            german: word.german,
            norsk: word.norsk
        }
    }
}

async function doneEdit(word: any): Promise<void> {
    const original = oldWord.value[word.id] ?? null
    if (!original) {
        return
    }

    const isChanged = word.german !== original.german || word.norsk !== original.norsk

    if (!isChanged) {
        delete oldWord.value[word.id]
        return
    }

    try {
        if (isChanged && word.german && word.norsk) {
            await wordsStore.update(word)
        }
    } catch (error: any) {
        wordsStore.error = error.message
        word.german = original.german
        word.norsk = original.norsk
    }

    delete oldWord.value[word.id]
}

function onNorskInput(word: any, event: Event): void {
    const rawValue = (event.target as HTMLInputElement).value
    const trimmedValue = rawValue.trim()
    word.norsk = replaceSpecialChars(trimmedValue)
}

function handleFocus(word: any): void {
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

