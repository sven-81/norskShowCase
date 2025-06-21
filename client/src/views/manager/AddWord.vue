<script setup lang="ts">
import { Field, Form } from 'vee-validate'
import * as Yup from 'yup'
import { useRoute } from 'vue-router'
import { replaceSpecialChars } from '@/components/specialChars'
import { ref, watch } from 'vue'

import { useAlertStore, useManagerWordStore } from '@/stores'

const alertStore = useAlertStore()
const wordsStore = useManagerWordStore()
const route = useRoute()
const id = route.params.id

let title = 'Wörter hinzufügen'

const schema = Yup.object().shape({
  german: Yup.string().required('Deutsch muss ausgefüllt sein'),
  norsk: Yup.string().required('Norwegisch muss ausgefüllt sein')
})

const inputChar = ref('')

watch(inputChar, (newVal) => {
  inputChar.value = replaceSpecialChars(newVal)
})

async function onSubmit(values) {
  try {
    let message

    await wordsStore.add(values)
    message = 'Das Wort "' + values.norsk + '" | "' + values.german + '" wurde hinzugefügt'

    alertStore.success(message)
  } catch (error) {
    alertStore.mapWordError(error)
  }
}
</script>

<template>
  <h2>{{ title }}</h2>
  <Form @submit="onSubmit" :validation-schema="schema" v-slot="{ errors, isSubmitting }">
    <div v-show="isSubmitting" class="spinner"></div>
    <div class="form-row add-word">
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
          id="norsk"
          type="text"
          class="form-control"
          v-model="inputChar"
          placeholder="Norwegisch"
          :class="{ 'is-invalid': errors.norsk }"
        />
        <div class="invalid-feedback">{{ errors.norsk }}</div>
      </div>
      <div class="form-group">
        <button class="button button-primary" :disabled="isSubmitting">hinzufügen</button>
      </div>
    </div>
  </Form>
</template>
