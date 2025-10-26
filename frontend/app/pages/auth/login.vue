<script setup lang="ts">
const { login } = useAuth()
const config = useRuntimeConfig()
const googleAuthUrl = computed(() => `${config.public.apiBase}/auth/oauth/google`)
const router = useRouter()

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const state = reactive({
  loading: false,
  error: ''
})

const submit = async () => {
  state.error = ''
  state.loading = true

  try {
    await login({ ...form })
    await router.push('/dashboard')
  } catch (error: any) {
    state.error = error?.data?.message || 'No se pudo iniciar sesión. Revisa tus credenciales.'
  } finally {
    state.loading = false
  }
}

definePageMeta({
  middleware: 'guest'
})

const loginWithGoogle = () => {
  window.location.href = googleAuthUrl.value
}
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>Inicia sesión</h1>
      <p class="muted">Usa tus credenciales para entrar al panel.</p>

      <form class="form" @submit.prevent="submit">
        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" required />
        </label>

        <label>
          <span>Contraseña</span>
          <input v-model="form.password" type="password" autocomplete="current-password" required />
        </label>

        <label class="checkbox">
          <input v-model="form.remember" type="checkbox" />
          <span>Mantener sesión iniciada</span>
        </label>

        <p v-if="state.error" class="error">{{ state.error }}</p>

        <button class="btn primary" type="submit" :disabled="state.loading">
          {{ state.loading ? 'Accediendo...' : 'Entrar' }}
        </button>
      </form>

      <div class="oauth">
        <span class="muted small">O continúa con</span>
        <button class="btn secondary" type="button" @click="loginWithGoogle">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M21.35 11.1h-9.4v2.97h5.57c-.24 1.32-1.13 2.44-2.4 3.18v2.64h3.88c2.27-2.09 3.35-5.36 2.34-8.79Z"
              fill="#4285F4"
            />
            <path
              d="M11.95 22c2.71 0 4.99-.9 6.65-2.41l-3.88-2.64c-1.08.72-2.47 1.15-3.98 1.15-3.05 0-5.63-2.05-6.55-4.81H.24v3.02C1.88 19.84 6.57 22 11.95 22Z"
              fill="#34A853"
            />
            <path
              d="M5.4 13.29c-.24-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V5.69H.24C-.2 6.88-.2 8.24.24 9.53c.64 1.99 1.86 3.77 3.5 5.06l1.66-1.3Z"
              fill="#FBBC05"
            />
            <path
              d="M11.95 4.79c1.47 0 2.8.51 3.85 1.5l2.88-2.88C17 .84 14.66 0 11.95 0 6.57 0 1.88 2.16.24 5.69l4.16 3.02c.92-2.76 3.5-4.92 6.55-4.92Z"
              fill="#EA4335"
            />
          </svg>
          Google
        </button>
      </div>

      <p class="muted small">
        ¿Aún no tienes cuenta?
        <NuxtLink to="/auth/register">Crea una ahora</NuxtLink>
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
  width: min(420px, 100%);
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
  margin-bottom: 2rem;
}

.small {
  margin-top: 1.5rem;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.9rem;
}

input[type='email'],
input[type='password'] {
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.3);
  background: rgba(15, 23, 42, 0.6);
  color: #f8fafc;
  padding: 0.85rem 1rem;
}

input:focus {
  outline: 2px solid #6366f1;
  outline-offset: 1px;
}

.checkbox {
  flex-direction: row;
  align-items: center;
  gap: 0.5rem;
}

.checkbox input {
  width: 1rem;
  height: 1rem;
}

.btn {
  border: none;
  border-radius: 999px;
  font-weight: 600;
  padding: 0.9rem;
}

.primary {
  background: linear-gradient(120deg, #6366f1, #8b5cf6, #ec4899);
  color: #fff;
}

.secondary {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.4);
  color: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.secondary svg {
  width: 1.1rem;
  height: 1.1rem;
}

.oauth {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
  text-align: center;
}

.error {
  color: #fca5a5;
  font-size: 0.9rem;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
