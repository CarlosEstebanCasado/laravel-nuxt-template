import { test, expect, type Page } from '@playwright/test'
import { login, logout } from './helpers/auth'

const deleteEmail = process.env.E2E_DELETE_EMAIL ?? 'deleteuser@example.com'
const deletePassword = process.env.E2E_DELETE_PASSWORD ?? 'password'
const resetEmail = process.env.E2E_RESET_EMAIL ?? 'resetuser@example.com'
const resetToken = process.env.E2E_RESET_TOKEN ?? ''
const apiBaseUrl = process.env.PLAYWRIGHT_API_BASE_URL ?? 'https://api.project.dev'

const gotoDashboardRoute = async (page: Page, path: string) => {
  let loaded = false
  for (let attempt = 0; attempt < 3; attempt += 1) {
    await page.goto(path)
    await page.waitForTimeout(500)
    if (await page.getByText(/Failed to fetch dynamically imported module/i).count()) {
      await page.reload()
      continue
    }
    if (await page.getByText(/Website Expired/i).count()) {
      await page.waitForTimeout(500)
      continue
    }
    loaded = true
    break
  }
  if (!loaded) {
    throw new Error('No se pudo cargar la ruta del dashboard tras varios intentos.')
  }
}

const requestPasswordReset = async (
  page: Page,
  payload: { token: string; email: string; password: string; password_confirmation: string }
) => {
  await page.request.get(`${apiBaseUrl}/sanctum/csrf-cookie`)
  const cookies = await page.context().cookies(apiBaseUrl)
  const xsrfToken = cookies.find((cookie) => cookie.name === 'XSRF-TOKEN')
  const xsrfHeader = xsrfToken ? decodeURIComponent(xsrfToken.value) : ''

  return page.request.post(`${apiBaseUrl}/auth/reset-password`, {
    data: payload,
    headers: {
      Accept: 'application/json',
      'X-XSRF-TOKEN': xsrfHeader
    }
  })
}

test.describe('Auth flows', () => {
  test('login and logout', async ({ page }) => {
    await login(page)
    await logout(page)
    await page.goto('/dashboard')
    await expect(page).toHaveURL(/\/login/)
  })

  test('signup', async ({ page }) => {
    const unique = Date.now()
    await page.goto('/signup')
    await page.getByRole('textbox', { name: /nombre|name/i }).fill(`E2E User ${unique}`)
    await page.getByRole('textbox', { name: /email/i }).fill(`e2e_${unique}@example.com`)
    const passwordField = page.locator('input[name="password"]')
    const passwordConfirmationField = page.locator('input[name="password_confirmation"]')
    await passwordField.fill('')
    await passwordField.type('password123')
    await passwordConfirmationField.fill('')
    await passwordConfirmationField.type('password123')
    await page.getByRole('button', { name: /crear cuenta|create account/i }).click()
    await expect(page).toHaveURL(/verify-email/)
  })

  test('forgot password', async ({ page }) => {
    await page.goto('/forgot-password')
    await page.getByRole('textbox', { name: /email/i }).fill('test@example.com')
    await page.getByRole('button', { name: /restablecimiento|reset|enviar/i }).click()
    await expect(page.getByText(/Revisa tu bandeja|Check your inbox/i)).toBeVisible()
  })

  test('reset password with invalid token', async ({ page }) => {
    const response = await requestPasswordReset(page, {
      token: 'invalid-token',
      email: resetEmail,
      password: 'password123',
      password_confirmation: 'password123'
    })

    expect(response.status()).toBe(422)
  })

  test('reset password with valid token', async ({ page }) => {
    if (!resetToken) {
      throw new Error('Missing E2E_RESET_TOKEN for reset password test.')
    }
    const response = await requestPasswordReset(page, {
      token: resetToken,
      email: resetEmail,
      password: 'password123',
      password_confirmation: 'password123'
    })

    expect(response.ok()).toBeTruthy()
  })

  test('delete account', async ({ page }) => {
    await login(page, { email: deleteEmail, password: deletePassword })
    await gotoDashboardRoute(page, '/dashboard/settings/security')
    await page.getByRole('button', { name: /Eliminar cuenta|Delete account|Eliminar compte/i }).click()
    await page.locator('input[placeholder="DELETE"]').fill('DELETE')
    await page
      .getByPlaceholder(/Tu contraseña actual|Your current password|La teva contrasenya actual/i)
      .fill(deletePassword)
    await page.getByRole('button', { name: /Eliminar permanentemente|Delete permanently|Eliminar definitivament/i }).click()
    await expect(page).toHaveURL(/signup/)
  })
})
