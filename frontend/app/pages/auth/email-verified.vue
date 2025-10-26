<script setup lang="ts">
const { fetchUser } = useAuth()

await fetchUser({ silent: true })
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>¡Email verificado!</h1>
      <p class="muted">
        Tu correo fue confirmado correctamente. Si la sesión sigue activa puedes ir directo al dashboard, de lo contrario
        vuelve a iniciar sesión y continúa donde lo dejaste.
      </p>

      <div class="actions">
        <NuxtLink class="btn primary" to="/dashboard">Ir al dashboard</NuxtLink>
        <NuxtLink class="btn ghost" to="/auth/login">Iniciar sesión</NuxtLink>
      </div>
    </section>
  </main>
</template>

<style scoped>
.auth-page {
  min-height: calc(100vh - 4rem);
  display: grid;
  place-items: center;
  padding: 1.5rem;
}

.card {
  width: min(480px, 100%);
  background: rgba(15, 23, 42, 0.92);
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  padding: 2rem;
  text-align: center;
}

.muted {
  color: #cbd5f5;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1rem;
}

.btn {
  border-radius: 999px;
  padding: 0.85rem 1.5rem;
  font-weight: 600;
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
