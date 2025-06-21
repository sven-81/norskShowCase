<script setup lang="ts">
import { ErrorMessage, Field, Form } from 'vee-validate'
import * as Yup from 'yup'
import { computed } from 'vue'
import { useAuthStore } from '@/stores'
import CookieConsent from '@/components/CookieConsent.vue'

const schema = computed(() =>
  Yup.object({
    username: Yup.string().required('Benutzername fehlt'),
    password: Yup.string().required('Passwort fehlt')
  })
)

const onSubmit = async (values: { username: string; password: string }) => {
  const authStore = useAuthStore()
  await authStore.login(values.username, values.password)
}
</script>

<template>
  <CookieConsent />
  <div>
    <h1>Login</h1>
    <div>
      <Form
        :validation-schema="schema"
        :initial-values="{ username: '', password: '' }"
        @submit="onSubmit"
        v-slot="{ errors, isSubmitting }"
      >
        <div v-show="isSubmitting" class="spinner">Loading...</div>

        <div class="form-group">
          <label for="username">Benutzername</label>
          <Field
            name="username"
            type="text"
            id="username"
            class="form-control"
            :class="{ 'is-invalid': errors.username }"
          />
          <ErrorMessage name="username" class="invalid-feedback" />
        </div>

        <div class="form-group">
          <label for="password">Passwort</label>
          <Field
            name="password"
            type="password"
            id="password"
            class="form-control"
            :class="{ 'is-invalid': errors.password }"
          />
          <ErrorMessage name="password" class="invalid-feedback" />
        </div>

        <div class="form-group">
          <button class="button button-primary" :disabled="isSubmitting">login</button>
          <router-link to="register" class="button button-secondary">registrieren</router-link>
        </div>
      </Form>
    </div>
  </div>
</template>
