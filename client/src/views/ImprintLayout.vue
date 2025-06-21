<script setup lang="ts">
const mail: string = `${import.meta.env.VITE_IMPRINT_MAIL}`
</script>

<template>
  <h1>Impressum</h1>
  <p class="imprint">
    Dies ist eine private Website zum Lernen von norwegischen Vokabeln.<br /><br />
    Kontaktieren kannst du mich über: {{ mail }}<br />
    Deine Freischaltung erfolgt nach Registrierung manuell. <br /><br />
    Wir verwenden Cookies, damit du dich registrieren und einloggen kannst. Dafür brauchen und
    speichern wir auch deine E-Mail-Adresse, sowie dass du mit dem Cookie einverstanden bist.<br />
    Damit der Vokabeltrainer funktioniert wird auch gespeichert, wann du was erfolgreich geübt
    hast.<br /><br />
    Willst du Auskunft über deine gespeicherten Daten oder möchtest deine Daten gelöscht haben,
    schreibe mich einfach an.
  </p>
</template>
