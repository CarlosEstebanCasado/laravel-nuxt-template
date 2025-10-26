<script setup lang="ts">
const { user, fetchUser, logout } = useAuth()
const router = useRouter()

if (!user.value) {
  await fetchUser({ silent: true })
}

definePageMeta({
  middleware: 'auth'
})

const handleLogout = async () => {
  await logout()
  await router.push('/auth/login')
}
</script>

<template>
  <main class="dashboard">
    <section class="card">
      <p class="eyebrow">Panel</p>
      <h1>Hola, {{ user?.name }}</h1>
      <p class="muted">
        Ya puedes usar esta plantilla para construir tu SaaS. Añade módulos, componentes y navegación sobre este layout.
      </p>

      <div class="profile">
        <div>
          <p class="label">Email</p>
          <p>{{ user?.email }}</p>
        </div>
        <div>
          <p class="label">Verificado</p>
          <p>{{ user?.email_verified_at ? 'Sí' : 'No' }}</p>
        </div>
        <div>
          <p class="label">Miembro desde</p>
          <p>{{ user?.created_at && new Date(user.created_at).toLocaleDateString() }}</p>
        </div>
      </div>

      <div class="actions">
        <NuxtLink class="btn ghost" to="/">Volver al inicio</NuxtLink>
        <button class="btn danger" @click="handleLogout">Cerrar sesión</button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.dashboard {
  min-height: calc(100vh - 4rem);
  display: grid;
  place-items: center;
  padding: 2rem;
}

.card {
  width: min(720px, 100%);
  background: rgba(15, 23, 42, 0.85);
  border-radius: 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  padding: 2.5rem;
  box-shadow: 0 25px 80px rgba(2, 6, 23, 0.65);
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.75rem;
  color: #8b5cf6;
  margin-bottom: 0.5rem;
}

.muted {
  color: #cbd5f5;
  margin-bottom: 2rem;
}

.profile {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.label {
  text-transform: uppercase;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.35rem;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.btn {
  border-radius: 999px;
  padding: 0.85rem 1.5rem;
  font-weight: 600;
  border: 1px solid transparent;
}

.ghost {
  border-color: rgba(148, 163, 184, 0.4);
  color: #e2e8f0;
}

.danger {
  background: linear-gradient(120deg, #fb7185, #f97316);
  color: #fff;
  border: none;
}
</style>
