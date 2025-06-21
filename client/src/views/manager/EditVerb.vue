<script setup lang="ts">
import { computed, ref } from 'vue'
import { useManagerVerbStore } from '@/stores'
import { replaceSpecialChars } from '@/components/specialChars'

const oldVerb = ref<
  Record<
    number,
    {
      german: string
      norsk: string
      norskPresent: string
      norskPast: string
      norskPastPerfect: string
    }
  >
>({})

const verbsStore = useManagerVerbStore()
const computedFilteredVerbs = computed(() => verbsStore.computedFilteredVerbs)

function sanitizeVerbField(verb, fieldName: keyof typeof verb) {
  verb[fieldName] = replaceSpecialChars(verb[fieldName])
}

function cacheOriginal(verb) {
  if (!oldVerb.value[verb.id]) {
    oldVerb.value[verb.id] = {
      german: verb.german,
      norsk: verb.norsk,
      norskPresent: verb.norskPresent,
      norskPast: verb.norskPast,
      norskPastPerfect: verb.norskPastPerfect
    }
  }
}

async function doneEdit(verb) {
  function deleteOldVerbToSaveMemory() {
    delete oldVerb.value[verb.id]
  }

  const original = oldVerb.value[verb.id] ?? null
  if (!original) return

  const isChanged =
    verb.german !== original.german ||
    verb.norsk !== original.norsk ||
    verb.norskPresent !== original.norskPresent ||
    verb.norskPast !== original.norskPast ||
    verb.norskPastPerfect !== original.norskPastPerfect

  if (!isChanged) {
    deleteOldVerbToSaveMemory()
    return
  }

  try {
    if (
      isChanged &&
      verb.german &&
      verb.norsk &&
      verb.norskPresent &&
      verb.norskPast &&
      verb.norskPastPerfect
    ) {
      await verbsStore.update(verb)
    }
  } catch (error) {
    verbsStore.error = error.message

    verb.german = original.german
    verb.norsk = original.norsk
    verb.norskPresent = original.norskPresent
    verb.norskPast = original.norskPast
    verb.norskPastPerfect = original.norskPastPerfect
  }

  deleteOldVerbToSaveMemory()
}

function handleFocus(verb) {
  verbsStore.clearError()
  cacheOriginal(verb)
}
</script>

<template>
  <h2>Verben bearbeiten</h2>
  <div id="edit-verbs">
    <div class="table">
      <template v-if="computedFilteredVerbs.length">
        <template v-for="verb in computedFilteredVerbs" :key="verb.id">
          <div class="cell">
            <label class="header-id">Id</label>
            {{ verb.id }}
          </div>
          <div class="cell">
            <label>Deutsch</label>
            <input
              class="form-control"
              v-model.trim="verb.german"
              @focus="handleFocus(verb)"
            />
          </div>
          <div class="cell">
            <label>Infinitiv</label>
            <input
              class="form-control"
              :id="'norsk_' + verb.id"
              v-model.trim="verb.norsk"
              @focus="handleFocus(verb)"
              @input="sanitizeVerbField(verb, 'norsk')"
            />
          </div>
          <div class="cell">
            <label>Präsens</label>
            <input
              class="form-control"
              :id="'norskPresent_' + verb.id"
              v-model.trim="verb.norskPresent"
              @focus="handleFocus(verb)"
              @input="sanitizeVerbField(verb, 'norskPresent')"
            />
          </div>
          <div class="cell">
            <label>Vergangenheit</label>
            <input
              class="form-control"
              :id="'norskPast_' + verb.id"
              v-model.trim="verb.norskPast"
              @focus="handleFocus(verb)"
              @input="sanitizeVerbField(verb, 'norskPast')"
            />
          </div>
          <div class="cell">
            <label>2. Vergangenheit</label>
            <input
              class="form-control"
              :id="'norskPastPerfect_' + verb.id"
              v-model.trim="verb.norskPastPerfect"
              @focus="handleFocus(verb)"
              @input="sanitizeVerbField(verb, 'norskPastPerfect')"
            />
          </div>
          <div class="buttons">
            <div class="cell">
              <button @click="doneEdit(verb)" class="button button-primary">speichern</button>
            </div>
            <div class="cell">
              <button
                @click="verbsStore.delete(verb.id)"
                class="button-delete"
                :disabled="verb.isDeleting"
              >
                <span>löschen</span>
              </button>
            </div>
          </div>
        </template>
      </template>
    </div>

    <div v-if="verbsStore.loading">
      <div class="spinner"></div>
    </div>

    <div v-if="verbsStore.error">
      <div class="text-danger">
        <p>{{ verbsStore.error }}</p>
      </div>
    </div>
  </div>
</template>
