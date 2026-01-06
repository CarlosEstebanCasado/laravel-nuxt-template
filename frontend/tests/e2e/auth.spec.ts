import { test, expect } from '@playwright/test'
import { login, logout } from './helpers/auth'

test.describe('Auth flows', () => {
  test('login and logout', async ({ page }) => {
    await login(page)
    await logout(page)
  })

  test('signup', async ({ page }) => {
    const unique = Date.now()
    await page.goto('/signup')
    await page.getByRole('textbox', { name: /nombre|name/i }).fill(`E2E User ${unique}`)
    await page.getByRole('textbox', { name: /email/i }).fill(`e2e_${unique}@example.com`)
    await page.getByLabel(/contraseña|password/i).first().fill('password')
    await page.getByLabel(/confirmar contraseña|confirm password/i).fill('password')
    await page.getByRole('button', { name: /crear cuenta|create account/i }).click()
    await expect(page).toHaveURL(/verify-email/)
  })

  test('forgot password', async ({ page }) => {
    await page.goto('/forgot-password')
    await page.getByRole('textbox', { name: /email/i }).fill('test@example.com')
    await page.getByRole('button', { name: /restablecimiento|reset|enviar/i }).click()
    await expect(page.getByText(/Revisa tu bandeja|Check your inbox/i)).toBeVisible()
  })
})
