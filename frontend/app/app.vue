<template>
  <NuxtLayout>
    <NuxtLoadingIndicator color="#818cf8" />
    <NuxtRouteAnnouncer />
    <NuxtPage />
  </NuxtLayout>
</template>

<style>
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
