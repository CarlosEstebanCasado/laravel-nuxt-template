<script setup lang="ts">
const { user, resendEmailVerification, fetchUser, logout } = useAuth()
const router = useRouter()

const state = reactive({
  loading: false,
  success: '',
  error: ''
})

const sendEmail = async () => {
  state.loading = true
  state.error = ''

  try {
    await resendEmailVerification()
    state.success = 'Enviamos un nuevo correo de verificación. Revisa tu bandeja.'
    await fetchUser({ force: true, silent: true })
  } catch (error: any) {
    state.error = error?.data?.message || 'No pudimos enviar el correo. Inténtalo en unos minutos.'
  } finally {
    state.loading = false
  }
}

const handleLogout = async () => {
  await logout()
  await router.push('/auth/login')
}

definePageMeta({
  middleware: 'auth'
})
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>Confirma tu email</h1>
      <p class="muted">
        Hola {{ user?.name }}. Te enviamos un enlace de verificación a <strong>{{ user?.email }}</strong>.
        Haz clic en el botón para reenviarlo si no lo encuentras.
      </p>

      <div class="actions">
        <button class="btn primary" :disabled="state.loading" @click="sendEmail">
          {{ state.loading ? 'Enviando...' : 'Reenviar correo' }}
        </button>
        <button class="btn ghost" @click="handleLogout">Cerrar sesión</button>
      </div>

      <p v-if="state.success" class="success">{{ state.success }}</p>
      <p v-if="state.error" class="error">{{ state.error }}</p>

      <p class="muted small">
        Una vez que hagas clic en el enlace del correo actualiza esta página. Te redirigiremos al dashboard.
      </p>
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
  width: min(520px, 100%);
  background: rgba(15, 23, 42, 0.92);
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  padding: 2rem;
}

h1 {
  margin-bottom: 0.25rem;
}

.muted {
  color: #cbd5f5;
  margin-bottom: 1.5rem;
}

.muted strong {
  color: #fff;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}

.btn {
  border-radius: 999px;
  border: none;
  font-weight: 600;
  padding: 0.85rem 1.5rem;
}

.primary {
  background: linear-gradient(120deg, #6366f1, #8b5cf6, #ec4899);
  color: #fff;
}

.ghost {
  border: 1px solid rgba(148, 163, 184, 0.4);
  background: transparent;
  color: #e2e8f0;
}

.error {
  color: #fca5a5;
}

.success {
  color: #6ee7b7;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
