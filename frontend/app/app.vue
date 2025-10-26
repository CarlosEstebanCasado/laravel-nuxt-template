<template>
  <div class="app-shell">
    <NuxtLoadingIndicator color="#818cf8" />
    <NuxtRouteAnnouncer />
    <NuxtPage />
  </div>
</template>

<style>
:root {
  color-scheme: dark;
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  margin: 0;
  min-height: 100vh;
  background: radial-gradient(circle at top, #0f172a, #050814 60%);
  color: #e2e8f0;
}

.app-shell {
  min-height: 100vh;
}

a {
  color: #93c5fd;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}

input,
button,
textarea {
  font: inherit;
}

button {
  cursor: pointer;
}
</style>
