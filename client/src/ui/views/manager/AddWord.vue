<script setup lang="ts">
import { Field, Form } from 'vee-validate'
import * as Yup from 'yup'
import { replaceSpecialChars } from '@/domain/service/SpecialCharReplacer'
import { ref, watch } from 'vue'
import { useAlertStore } from '@/infrastructure/store/alert.store'
import { useManagerWordStore } from '@/infrastructure/store/managerWord.store'

const alertStore = useAlertStore()
const wordsStore = useManagerWordStore()

const title = 'Wörter hinzufügen'

const schema = Yup.object().shape({
    german: Yup.string().required('Deutsch muss ausgefüllt sein'),
    norsk: Yup.string().required('Norwegisch muss ausgefüllt sein')
})

const inputChar = ref('')

watch(inputChar, (newVal) => {
    inputChar.value = replaceSpecialChars(newVal)
})

async function onSubmit(values: any): Promise<void> {
    try {
        await wordsStore.add(values)
        const message = 'Das Wort "' + values.norsk + '" | "' + values.german + '" wurde hinzugefügt'
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

