<script lang="ts">
import {defineComponent, onMounted, ref} from 'vue';
import Cookies from 'js-cookie';

export default defineComponent({
  name: 'CookiePopup',
  setup() {
    // Ref für die Zustimmung des Benutzers
    const cookieConsentGiven = ref<boolean>(false);

    // Prüfen, ob der Benutzer bereits zugestimmt hat und das Cookie gültig ist
    onMounted(() => {
      const consent = Cookies.get('cookieConsent');
      if (consent === 'true') {
        cookieConsentGiven.value = true;
      }
    });

    const acceptCookies = () => {
      const days = 30;
      Cookies.set('cookieConsent', 'true', {expires: days, secure: true, sameSite: 'Strict'});
      cookieConsentGiven.value = true;
    };

    return {
      cookieConsentGiven,
      acceptCookies
    };
  }
});
</script>

<template>
  <div v-if="!cookieConsentGiven" class="cookie-popup">
    <div class="cookie-popup-content">
      <p>
        Wir verwenden Cookies für die Benutzerverwaltung. <br/>
        Mit der Nutzung dieser Webseite stimmst Du der Verwendung von Cookies zu, sonst funktioniert der Vokabeltrainer
        nicht.
      </p>
      <button class="accept" @click="acceptCookies">Ich stimme zu</button>
    </div>
  </div>
</template>

<style scoped>
.cookie-popup {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: rgba(255, 255, 255, 0.5);
  color: #000;
  padding: 1rem;
  text-align: center;
  font-size: 1.25rem;
  z-index: 1000;
}

.cookie-popup-content {
  display: inline-block;
  background-color: #fff;
  padding: 1rem;
  box-shadow: inset 0 0.125rem 0.25rem rgba(0, 0, 0, 0.175);
}

.cookie-popup button {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 1.25rem;
}

.accept {
  background-color: #44a147;
  margin-right: 1rem;
}

.cookie-popup button.accept:hover {
  background-color: #037509;
}
</style>
