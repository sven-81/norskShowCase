<script setup lang="ts">
import {useAuthStore} from "@/stores";
import {useRoute} from "vue-router";

const authStore = useAuthStore();
const route = useRoute();
const isActive = (path: string) => {
  return route.path.startsWith(path);
};
</script>

<template>
  <nav class="menu">
    <ol>
      <li :class="[ { active: isActive('/train') }]">
        <a href="">Trainer</a>
        <ol class="sub-menu">
          <li :class="[ { active: isActive('/train/words') }]"><a href="/train/words">Wörter</a></li>
          <li :class="[ { active: isActive('/train/verbs') }]"><a href="/train/verbs">Verben</a></li>
        </ol>
      </li>
      <li :class="[ { active: isActive('/manage') }]"
          v-if="authStore.user && authStore.user.scope === 'is:manager'">
        <a href="">Manager</a>
        <ol class="sub-menu">
          <li :class="[ { active: isActive('/manage/words') }]"><a href="/manage/words">Wörter</a></li>
          <li :class="[ { active: isActive('/manage/verbs') }]"><a href="/manage/verbs">Verben</a></li>
        </ol>
      </li>
      <li v-if="authStore.user" v-on:click="authStore.logout()">
        <a href="/login">Logout</a>
      </li>
      <template v-else>
        <li :class="[ { active: isActive('/login') }]">
          <a href="/login">Login</a>
        </li>
        <li :class="[ { active: isActive('/register') }]">
          <a href="/register">Registrieren</a>
        </li>
      </template>
      <li :class="[ { active: isActive('/imprint') }]">
        <a href="/imprint">Impressum</a>
      </li>
    </ol>
  </nav>
</template>
