<script setup lang="ts">
import { Alert } from '@/components'
import MainNav from '@/views/MainNav.vue'

const mail: string = `${import.meta.env.VITE_IMPRINT_MAIL}`
</script>

<template>
  <div class="wrapper">
    <header id="header" class="header">
      <h1>
        <a href="/"
          ><img
            alt="Norwegen Fahne"
            class="norway-flag"
            src="../../src/assets/images/norway.svg"
          />NORSK</a
        >
      </h1>
      <h2>Norwegisch lernen</h2>
    </header>

    <MainNav />
    <Alert />
    <div id="content" class="column row">
      <router-view />
    </div>

    <footer class="column">
      <div class="row">
        <div class="copyright icon-paw">
          <p>
            <a v-bind:href="`mailto:${mail}`" v-bind:title="`Mail an ${mail}`">{{ mail }}</a>
            <br />
            <span class="small">flag: publicdomainvectors.org</span>
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
