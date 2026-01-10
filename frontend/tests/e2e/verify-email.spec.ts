import { test, expect } from '@playwright/test'

const unverifiedEmail = process.env.E2E_UNVERIFIED_EMAIL ?? 'unverified@example.com'
const unverifiedPassword = process.env.E2E_UNVERIFIED_PASSWORD ?? 'password'

test.describe('Email verification', () => {
  test('unverified user lands on verify page and can resend email', async ({ page }) => {
    await page.goto('/login')
    await page.getByRole('textbox', { name: /email/i }).fill(unverifiedEmail)
    await page.locator('input[name="password"]').fill(unverifiedPassword)
    await page.getByRole('button', { name: /continuar|continue|iniciar|acceder/i }).click()

    await expect(page).toHaveURL(/\/auth\/verify-email/)

    await page.getByRole('button', { name: /reenviar|resend/i }).click()
    await expect(
      page.getByText(/correo de verificación enviado|verification email sent|correu de verificació enviat/i).first()
    ).toBeVisible()

    await page.getByRole('button', { name: /cerrar sesión|log out|tancar sessió/i }).click()
    await expect(page).toHaveURL(/\/login/)
  })
})
