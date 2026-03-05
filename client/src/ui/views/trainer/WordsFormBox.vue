<script setup lang="ts">
import { Field, Form } from 'vee-validate'
import * as Yup from 'yup'
import { useTrainerWordStore } from '@/infrastructure/store/trainerWord.store'
import { replaceSpecialChars } from '@/domain/service/SpecialCharReplacer'
import { ref, watch } from 'vue'

const activeWord = useTrainerWordStore()

const schema = Yup.object().shape({
    norsk: Yup.string().required('Bitte gebe oben das norwegische Wort ein.')
})

const inputChar = ref('')

async function onSubmit(values: any): Promise<void> {
    const cleanInput = replaceSpecialChars(values.norsk.trim())
    await activeWord.evaluate(cleanInput)
}

watch(inputChar, (newVal) => {
    inputChar.value = replaceSpecialChars(newVal)
})
</script>

<template>
  <Form
    @submit="onSubmit"
    :validation-schema="schema"
    v-slot="{ errors }"
    class="form-horizontal"
    autocomplete="off"
  >
    <fieldset>
      <legend>Wörtersammlung</legend>
      <div class="form-group">
        <label class="col-md-4 control-label" for="norsk">Norsk</label>
        <div class="col-md-5">
          <Field
            id="norsk"
            name="norsk"
            type="text"
            autofocus="autofocus"
            tabindex="1"
            placeholder="Norsk"
            class="form-control"
            required
            v-model="inputChar"
            onfocus="this.value=''"
          />
          <div class="invalid-feedback">{{ errors.norsk }}</div>
          <br />
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-4 control-label" for="button-check"></label>
        <div class="col-md-4">
          <button
            type="submit"
            id="button-check"
            tabindex="6"
            class="button button-primary button-action"
          >
            prüfen
          </button>
        </div>
      </div>
    </fieldset>
  </Form>
</template>

