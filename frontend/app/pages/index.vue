<template>
  <main class="page">
    <section class="card">
      <p class="eyebrow">Template SaaS</p>
      <h1>Laravel + Nuxt listo para autenticación</h1>
      <p class="muted">
        Inicia sesión, registra nuevos usuarios y verifica correos con unos clics. Usa esta plantilla como
        base para tu próximo producto SaaS.
      </p>
      <div class="actions">
        <NuxtLink to="/auth/register" class="btn primary">Crear cuenta</NuxtLink>
        <NuxtLink to="/auth/login" class="btn ghost">Ya tengo cuenta</NuxtLink>
      </div>
    </section>
  </main>
</template>

<style scoped>
.page {
  min-height: calc(100vh - 4rem);
  display: grid;
  place-items: center;
  padding: 2rem;
}

.card {
  width: min(640px, 100%);
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  padding: 2.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 60px rgba(2, 6, 23, 0.6);
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.75rem;
  color: #818cf8;
  margin-bottom: 0.75rem;
}

h1 {
  font-size: clamp(2rem, 4vw, 2.75rem);
  margin-bottom: 1rem;
}

.muted {
  color: #cbd5f5;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.btn {
  border-radius: 999px;
  padding: 0.75rem 1.75rem;
  font-weight: 600;
  text-align: center;
}

.primary {
  background: linear-gradient(120deg, #6366f1, #8b5cf6, #ec4899);
  color: #fff;
}

.ghost {
  border: 1px solid rgba(148, 163, 184, 0.4);
  color: #e2e8f0;
}
</style>
