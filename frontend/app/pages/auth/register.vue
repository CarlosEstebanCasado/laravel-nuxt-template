<script setup lang="ts">
const { register } = useAuth()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const state = reactive({
  loading: false,
  error: ''
})

const submit = async () => {
  state.error = ''
  state.loading = true

  if (form.password !== form.password_confirmation) {
    state.error = 'Las contraseñas no coinciden.'
    state.loading = false
    return
  }

  try {
    await register({ ...form })
    await router.push('/auth/verify-email')
  } catch (error: any) {
    state.error = error?.data?.message || 'No se pudo crear la cuenta. Revisa los datos e inténtalo de nuevo.'
  } finally {
    state.loading = false
  }
}

definePageMeta({
  middleware: 'guest'
})
</script>

<template>
  <main class="auth-page">
    <section class="card">
      <h1>Crea tu cuenta</h1>
      <p class="muted">Recibirás un correo para confirmar tu email antes de acceder.</p>

      <form class="form" @submit.prevent="submit">
        <label>
          <span>Nombre completo</span>
          <input v-model="form.name" type="text" autocomplete="name" required />
        </label>

        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="email" required />
        </label>

        <label>
          <span>Contraseña</span>
          <input v-model="form.password" type="password" autocomplete="new-password" required />
        </label>

        <label>
          <span>Confirmar contraseña</span>
          <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
        </label>

        <p v-if="state.error" class="error">{{ state.error }}</p>

        <button class="btn primary" type="submit" :disabled="state.loading">
          {{ state.loading ? 'Creando cuenta...' : 'Registrarme' }}
        </button>
      </form>

      <p class="muted small">
        ¿Ya tienes cuenta?
        <NuxtLink to="/auth/login">Inicia sesión</NuxtLink>
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

input[type='text'],
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

.error {
  color: #fca5a5;
  font-size: 0.9rem;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
