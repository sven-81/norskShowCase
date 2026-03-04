<script setup lang="ts">
import { computed, ref } from 'vue'
import { useManagerVerbStore } from '@/infrastructure/store/managerVerb.store'
import { replaceSpecialChars } from '@/domain/service/SpecialCharReplacer'

const oldVerb = ref<Record<number, {
    german: string
    norsk: string
    norskPresent: string
    norskPast: string
    norskPastPerfect: string
}>>({})

const verbsStore = useManagerVerbStore()
const computedFilteredVerbs = computed(() => verbsStore.computedFilteredVerbs)

function sanitizeVerbField(verb: any, fieldName: string): void {
    verb[fieldName] = replaceSpecialChars(verb[fieldName])
}

function cacheOriginal(verb: any): void {
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

async function doneEdit(verb: any): Promise<void> {
    const original = oldVerb.value[verb.id] ?? null
    if (!original) {
        return
    }

    const isChanged =
        verb.german !== original.german ||
        verb.norsk !== original.norsk ||
        verb.norskPresent !== original.norskPresent ||
        verb.norskPast !== original.norskPast ||
        verb.norskPastPerfect !== original.norskPastPerfect

    if (!isChanged) {
        delete oldVerb.value[verb.id]
        return
    }

    try {
        if (isChanged && verb.german && verb.norsk && verb.norskPresent && verb.norskPast && verb.norskPastPerfect) {
            await verbsStore.update(verb)
        }
    } catch (error: any) {
        verbsStore.error = error.message
        verb.german = original.german
        verb.norsk = original.norsk
        verb.norskPresent = original.norskPresent
        verb.norskPast = original.norskPast
        verb.norskPastPerfect = original.norskPastPerfect
    }

    delete oldVerb.value[verb.id]
}

function handleFocus(verb: any): void {
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
            <input class="form-control" v-model.trim="verb.german" @focus="handleFocus(verb)" />
          </div>
          <div class="cell">
            <label>Infinitiv</label>
            <input class="form-control" :id="'norsk_' + verb.id" v-model.trim="verb.norsk"
                   @focus="handleFocus(verb)" @input="sanitizeVerbField(verb, 'norsk')" />
          </div>
          <div class="cell">
            <label>Präsens</label>
            <input class="form-control" :id="'norskPresent_' + verb.id" v-model.trim="verb.norskPresent"
                   @focus="handleFocus(verb)" @input="sanitizeVerbField(verb, 'norskPresent')" />
          </div>
          <div class="cell">
            <label>Vergangenheit</label>
            <input class="form-control" :id="'norskPast_' + verb.id" v-model.trim="verb.norskPast"
                   @focus="handleFocus(verb)" @input="sanitizeVerbField(verb, 'norskPast')" />
          </div>
          <div class="cell">
            <label>2. Vergangenheit</label>
            <input class="form-control" :id="'norskPastPerfect_' + verb.id" v-model.trim="verb.norskPastPerfect"
                   @focus="handleFocus(verb)" @input="sanitizeVerbField(verb, 'norskPastPerfect')" />
          </div>
          <div class="buttons">
            <div class="cell">
              <button @click="doneEdit(verb)" class="button button-primary">speichern</button>
            </div>
            <div class="cell">
              <button @click="verbsStore.delete(verb.id)" class="button-delete" :disabled="verb.isDeleting">
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

