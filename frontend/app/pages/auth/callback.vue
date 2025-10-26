<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const { fetchUser } = useAuth()

const state = reactive({
  loading: true,
  status: (route.query.status as string) || 'pending',
  provider: (route.query.provider as string) || '',
  error: (route.query.error as string) || ''
})

onMounted(async () => {
  if (state.status !== 'success') {
    state.loading = false
    return
  }

  try {
    await fetchUser({ force: true })
    await router.replace('/dashboard')
  } catch (error) {
    state.status = 'error'
    state.error = 'No pudimos completar el inicio de sesión.'
  } finally {
    state.loading = false
  }
})
</script>

<template>
  <main class="callback">
    <section class="card">
      <div v-if="state.loading" class="spinner" aria-label="Cargando sesión" />

      <template v-else>
        <h1 v-if="state.status === 'success'">¡Listo!</h1>
        <h1 v-else>Algo salió mal</h1>

        <p v-if="state.status === 'success'" class="muted">
          Estamos redirigiéndote a tu panel.
        </p>

        <div v-else class="error">
          <p> No pudimos iniciar sesión con {{ state.provider || 'el proveedor' }}.</p>
          <p v-if="state.error"><small>Detalle: {{ state.error }}</small></p>
          <NuxtLink to="/auth/login" class="btn">Volver al login</NuxtLink>
        </div>
      </template>
    </section>
  </main>
</template>

<style scoped>
.callback {
  min-height: calc(100vh - 4rem);
  display: grid;
  place-items: center;
  padding: 1.5rem;
}

.card {
  width: min(360px, 100%);
  background: rgba(15, 23, 42, 0.92);
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  padding: 2rem;
  text-align: center;
}

.muted {
  color: #cbd5f5;
}

.spinner {
  width: 56px;
  height: 56px;
  margin: 0 auto;
  border: 4px solid rgba(148, 163, 184, 0.2);
  border-top-color: #818cf8;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  color: #fecaca;
}

.btn {
  display: inline-flex;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.4);
  border-radius: 999px;
  padding: 0.75rem 1.5rem;
  color: #f8fafc;
}
</style>
