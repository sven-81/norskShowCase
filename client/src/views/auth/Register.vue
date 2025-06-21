<script setup lang="ts">
import { Field, Form } from 'vee-validate'
import * as Yup from 'yup'

import { useUserStore } from '@/stores'

const schema = Yup.object().shape({
  firstName: Yup.string().required('Vorname fehlt'),
  lastName: Yup.string().required('Nachname fehlt'),
  username: Yup.string()
    .required('Benutzername fehlt')
    .min(4, 'Der Benutzername muss mindestens 4 Zeichen lang sein.')
    .max(30, 'Der Benutzername darf nicht länger als 30 Zeichen sein.')
    .matches(
      /^[a-zA-Z0-9_-]+$/,
      'Benutzername darf nur Buchstaben, Zahlen, Unterstriche und Bindestriche enthalten.'
    ),
  password: Yup.string()
    .required('Passwort fehlt')
    .min(12, 'Das Passwort muss mindestens 12 Zeichen lang sein.')
    .matches(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?^#_+\-=\.,:öäüÖÄÜß])[A-Za-z\d@$!%*?^#_+\-=\.,:öäüÖÄÜß]{12,}$/,
      'Das Passwort muss mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen (@$!%*?^#_+-=.,:) enthalten.'
    )
})

async function onSubmit(values) {
  const usersStore = useUserStore()

  await usersStore.register(values)
}
</script>

<template>
  <div class="card m-3">
    <h1>Registrierung</h1>
    <div>
      <Form @submit="onSubmit" :validation-schema="schema" v-slot="{ errors, isSubmitting }">
        <div v-show="isSubmitting" class="spinner"></div>
        <div class="form-group">
          <label>Vorname</label>
          <Field
            name="firstName"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.firstName }"
          />
          <div class="invalid-feedback">{{ errors.firstName }}</div>
        </div>
        <div class="form-group">
          <label>Nachname</label>
          <Field
            name="lastName"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.lastName }"
          />
          <div class="invalid-feedback">{{ errors.lastName }}</div>
        </div>
        <div class="form-group">
          <label>Benutzername</label>
          <Field
            name="username"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.username }"
          />
          <div class="invalid-feedback">{{ errors.username }}</div>
        </div>
        <div class="form-group">
          <label>Passwort</label>
          <Field
            name="password"
            type="password"
            class="form-control"
            :class="{ 'is-invalid': errors.password }"
          />
          <div class="invalid-feedback">{{ errors.password }}</div>
        </div>
        <div class="form-group">
          <button class="button button-primary" :disabled="isSubmitting">registrieren</button>
          <router-link to="login" class="button button-secondary">abbrechen</router-link>
        </div>
      </Form>
    </div>
  </div>
</template>
