<script setup lang="ts">
import { Field, Form, useForm } from 'vee-validate'
import * as Yup from 'yup'
import { ref } from 'vue'
import { replaceSpecialChars } from '@/components/specialChars'
import { useTrainerVerbStore } from '@/stores'

const activeVerb = useTrainerVerbStore();

const { setFieldValue } = useForm()

const schema = Yup.object({
  infinitive: Yup.string().required('Infinitiv wird gebraucht'),
  present: Yup.string().required('Präsens wird gebraucht'),
  past: Yup.string().required('Vergangenheit wird gebraucht'),
  pastPerfect: Yup.string().required('2. Vergangenheit wird gebraucht')
})

const infinitiveInput = ref('')
const presentInput = ref('')
const pastInput = ref('')
const pastPerfectInput = ref('')

function handleInput(field: 'infinitive' | 'present' | 'past' | 'pastPerfect', rawValue: string) {
  const converted = replaceSpecialChars(rawValue.trim())

  const setterMap: Record<string, (val: string) => void> = {
    infinitive: (val: string) => (infinitiveInput.value = val),
    present: (val: string) => (presentInput.value = val),
    past: (val: string) => (pastInput.value = val),
    pastPerfect: (val: string) => (pastPerfectInput.value = val)
  }

  setterMap[field](converted)
  setFieldValue(field, converted)
}

async function onSubmit(values) {
  const cleanedValues = {
    infinitive: replaceSpecialChars(values.infinitive.trim()),
    present: replaceSpecialChars(values.present.trim()),
    past: replaceSpecialChars(values.past.trim()),
    pastPerfect: replaceSpecialChars(values.pastPerfect.trim())
  }

  await activeVerb.evaluate(cleanedValues)
}
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
      <!-- Form Name -->
      <legend>Wörtersammlung</legend>

      <!-- Text input -->
      <div class="form-group">
        <label class="col-md-4 control-label" for="infinitive">Infinitiv</label>
        <div class="col-md-5">
          <Field name="infinitive" v-slot="{ field, meta, errorMessage }">
            <input
              v-bind="field"
              id="infinitive"
              class="form-control"
              :value="infinitiveInput"
              @input="(e) => handleInput('infinitive', e.target.value)"
              @focus="(e) => (e.target.value = '')"
              autofocus="autofocus"
              tabindex="1"
              placeholder="Infinitiv"
              required
            />
            <div class="invalid-feedback">{{ errorMessage }}</div>
          </Field>
          <br />
        </div>
        <label class="col-md-4 control-label" for="present">Präsens</label>
        <div class="col-md-5">
          <Field name="present" v-slot="{ field, meta, errorMessage }">
            <input
              v-bind="field"
              id="present"
              class="form-control"
              :value="presentInput"
              @input="(e) => handleInput('present', e.target.value)"
              @focus="(e) => (e.target.value = '')"
              autofocus="autofocus"
              tabindex="2"
              placeholder="Präsens"
              required
            />
            <div class="invalid-feedback">{{ errorMessage }}</div>
          </Field>
          <br />
        </div>
        <label class="col-md-4 control-label" for="past">Vergangenheit</label>
        <div class="col-md-5">
          <Field name="past" v-slot="{ field, meta, errorMessage }">
            <input
              v-bind="field"
              id="past"
              class="form-control"
              :value="pastInput"
              @input="(e) => handleInput('past', e.target.value)"
              @focus="(e) => (e.target.value = '')"
              autofocus="autofocus"
              tabindex="3"
              placeholder="Vergangenheit"
              required
            />
            <div class="invalid-feedback">{{ errorMessage }}</div>
          </Field>
          <br />
        </div>
        <label class="col-md-4 control-label" for="pastPerfect">2. Vergangenheit</label>
        <div class="col-md-5">
          <Field name="pastPerfect" v-slot="{ field, meta, errorMessage }">
            <input
              v-bind="field"
              id="pastPerfect"
              class="form-control"
              :value="pastPerfectInput"
              @input="(e) => handleInput('pastPerfect', e.target.value)"
              @focus="(e) => (e.target.value = '')"
              autofocus="autofocus"
              tabindex="4"
              placeholder="2. Vergangenheit"
              required
            />
            <div class="invalid-feedback">{{ errorMessage }}</div>
          </Field>
          <br />
        </div>
      </div>

      <!-- Button -->
      <div class="form-group">
        <label class="col-md-4 control-label" for="button-check"></label>
        <div class="col-md-4">
          <button
            type="submit"
            id="button-check"
            tabindex="5"
            class="button button-primary button-action"
          >
            prüfen
          </button>
        </div>
      </div>
    </fieldset>
  </Form>
</template>
