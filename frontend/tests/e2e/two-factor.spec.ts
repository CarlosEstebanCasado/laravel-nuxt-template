import { test, expect, type Page } from '@playwright/test'
import { authenticator } from 'otplib'
import { login } from './helpers/auth'

const password = process.env.E2E_2FA_PASSWORD ?? process.env.E2E_USER_PASSWORD ?? 'password'
const email = process.env.E2E_2FA_EMAIL ?? 'twofactor@example.com'

const gotoDashboardRoute = async (page: Page, path: string) => {
  for (let attempt = 0; attempt < 3; attempt += 1) {
    await page.goto(path)
    await page.waitForTimeout(500)
    if (await page.getByText(/Failed to fetch dynamically imported module/i).count()) {
      await page.reload()
      continue
    }
    break
  }
}

test.describe('Two-factor authentication', () => {
  test('enable, confirm, and disable 2FA', async ({ page }) => {
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

    await page.getByRole('button', { name: /Desactivar|Disable/i }).click()
    await page.getByPlaceholder(/Tu contraseña actual|current password/i).fill(password)
    await page.getByRole('button', { name: /Confirmar|Confirm/i }).click()
    await expect(page.getByText(/está desactivada|disabled/i)).toBeVisible()
  })
})
