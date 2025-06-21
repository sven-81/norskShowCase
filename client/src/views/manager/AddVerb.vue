<script setup lang="ts">
import { Field, Form } from 'vee-validate'
import * as Yup from 'yup'
import { useRoute } from 'vue-router'
import { replaceSpecialChars } from '@/components/specialChars'
import { ref, watch } from 'vue'

import { useAlertStore, useManagerVerbStore } from '@/stores'

const alertStore = useAlertStore()
const verbStore = useManagerVerbStore()
const route = useRoute()
const id = route.params.id

let title = 'Verben hinzufügen'

const schema = Yup.object().shape({
  german: Yup.string()
    .required('Deutsch muss ausgefüllt sein')
    .min(2, 'Das deutsche Verb muss mindestens 2 Zeichen lang sein.'),
  norsk: Yup.string().required('Norwegischer Infinitiv muss ausgefüllt sein'),
  norskPresent: Yup.string().required('Norwegisch Präsens muss ausgefüllt sein'),
  norskPast: Yup.string().required('Norwegisch Vergangenheit muss ausgefüllt sein'),
  norskPastPerfect: Yup.string().required('Norwegisch 2. Vergangenheit muss ausgefüllt sein')
})

// Reaktive Felder, um Sonderzeichen zu ersetzen
const norsk = ref('')
const norskPresent = ref('')
const norskPast = ref('')
const norskPastPerfect = ref('')

watch(norsk, (val) => {
  const converted = replaceSpecialChars(val)
  if (converted !== val) {
    norsk.value = converted
  }
})

watch(norskPresent, (val) => {
  const converted = replaceSpecialChars(val)
  if (converted !== val) {
    norskPresent.value = converted
  }
})

watch(norskPast, (val) => {
  const converted = replaceSpecialChars(val)
  if (converted !== val) {
    norskPast.value = converted
  }
})

watch(norskPastPerfect, (val) => {
  const converted = replaceSpecialChars(val)
  if (converted !== val) {
    norskPastPerfect.value = converted
  }
})

async function onSubmit(verbData) {
  try {
    let message

    await verbStore.add(verbData)
    message = `Das Verb "${verbData.norsk}" | "${verbData.german}" wurde hinzugefügt`

    alertStore.success(message)
  } catch (error) {
    alertStore.mapVerbError(error)
  }
}
</script>

<template>
  <h2>{{ title }}</h2>
  <Form @submit="onSubmit" :validation-schema="schema" v-slot="{ errors, isSubmitting }">
    <div v-show="isSubmitting" class="spinner"></div>
    <div class="form-row add-verb">
      <div class="form-group col">
        <Field
          name="german"
          type="text"
          class="form-control"
          placeholder="Deutsch"
          :class="{ 'is-invalid': errors.german }"
        />
        <div class="invalid-feedback">{{ errors.german }}</div>
      </div>
      <div class="form-group col">
        <Field
          name="norsk"
          type="text"
          class="form-control"
          v-model="norsk"
          placeholder="Infinitiv"
          :class="{ 'is-invalid': errors.norsk }"
        />
        <div class="invalid-feedback">{{ errors.norsk }}</div>
      </div>
      <div class="form-group col">
        <Field
          name="norskPresent"
          type="text"
          class="form-control"
          v-model="norskPresent"
          placeholder="Präsens"
          :class="{ 'is-invalid': errors.norskPresent }"
        />
        <div class="invalid-feedback">{{ errors.norskPresent }}</div>
      </div>
      <div class="form-group col">
        <Field
          name="norskPast"
          type="text"
          class="form-control"
          v-model="norskPast"
          placeholder="Vergangenheit"
          :class="{ 'is-invalid': errors.norskPast }"
        />
        <div class="invalid-feedback">{{ errors.norskPast }}</div>
      </div>
      <div class="form-group col">
        <Field
          name="norskPastPerfect"
          type="text"
          class="form-control"
          v-model="norskPastPerfect"
          placeholder="2. Vergangenheit"
          :class="{ 'is-invalid': errors.norskPastPerfect }"
        />
        <div class="invalid-feedback">{{ errors.norskPastPerfect }}</div>
      </div>
      <div class="form-group">
        <button class="button button-primary" :disabled="isSubmitting">hinzufügen</button>
      </div>
    </div>
  </Form>
</template>
