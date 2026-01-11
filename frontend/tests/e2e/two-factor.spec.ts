import { test, expect, type Page } from '@playwright/test'
import { authenticator } from 'otplib'
import { login } from './helpers/auth'

const password = process.env.E2E_2FA_PASSWORD ?? process.env.E2E_USER_PASSWORD ?? 'password'
const email = process.env.E2E_2FA_EMAIL ?? 'twofactor@example.com'

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

test.describe('Two-factor authentication', () => {
  test('enable, confirm, login with recovery code, and disable 2FA', async ({ page }) => {
    await login(page, { email, password })
    for (let attempt = 0; attempt < 3; attempt += 1) {
      await gotoDashboardRoute(page, '/dashboard/settings/security')
      const twoFactorButton = page.getByRole('button', { name: /Activar|Enable|Desactivar|Disable/i }).first()
      try {
        await twoFactorButton.waitFor({ state: 'visible', timeout: 5000 })
        await twoFactorButton.scrollIntoViewIfNeeded()
        break
      } catch {
        if (await page.getByText(/Failed to fetch dynamically imported module/i).count()) {
          continue
        }
        throw new Error('No se pudo cargar la sección de 2FA.')
      }
    }

    const disableButton = page.getByRole('button', { name: /Desactivar|Disable/i })

    if (await disableButton.isVisible()) {
      await disableButton.click()
      await page.getByPlaceholder(/Tu contraseña actual|current password/i).fill(password)
      await page.getByRole('button', { name: /Confirmar|Confirm/i }).click()
      await expect(page.getByText(/desactivada|disabled/i)).toBeVisible()
    }

    await page.getByRole('button', { name: /Activar|Enable/i }).click()
    await expect(page.getByText(/Escanea este código QR|QR/i)).toBeVisible()

    const secret = await page.locator('span.font-mono').first().innerText()
    const code = authenticator.generate(secret.trim())
    await page.getByPlaceholder(/Código de 6 dígitos|6-digit/i).fill(code)
    await page.getByRole('button', { name: /Confirmar|Confirm/i }).click()
    await expect(page.getByText(/está activa|enabled/i).first()).toBeVisible()

    const recoveryCode = await page.locator('div.font-mono span').first().innerText()

    await page.getByTestId('user-menu-trigger').click()
    await page.getByRole('menuitem', { name: /cerrar sesión|logout/i }).click()
    await expect(page).toHaveURL(/\/login/)

    await page.getByRole('textbox', { name: /email/i }).fill(email)
    await page.locator('input[name="password"]').fill(password)
    await page.getByRole('button', { name: /continuar|continue|iniciar|acceder/i }).click()
    await expect(page).toHaveURL(/\/auth\/two-factor/)

    await page.getByRole('combobox').click()
    await page.getByRole('option', { name: /Recovery code|Código de recuperación|Codi de recuperació/i }).click()
    await page.getByPlaceholder(/recovery code|código de recuperación|codi de recuperació/i).fill(recoveryCode)
    await page.getByRole('button', { name: /Verify|Verificar|Confirmar/i }).click()
    await expect(page).toHaveURL(/\/dashboard/)

    await gotoDashboardRoute(page, '/dashboard/settings/security')
    await page.getByRole('button', { name: /Desactivar|Disable/i }).click()
    await page.getByPlaceholder(/Tu contraseña actual|current password/i).fill(password)
    await page.getByRole('button', { name: /Confirmar|Confirm/i }).click()
    await expect(page.getByText(/está desactivada|disabled/i)).toBeVisible()
  })
})
