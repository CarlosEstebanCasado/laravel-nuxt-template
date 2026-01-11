import { test, expect, type Page } from '@playwright/test'
import { login } from './helpers/auth'

const profileEmail = process.env.E2E_PROFILE_EMAIL ?? 'profileuser@example.com'
const profilePassword = process.env.E2E_PROFILE_PASSWORD ?? 'password'
const passwordEmail = process.env.E2E_PASSWORD_EMAIL ?? 'passworduser@example.com'
const passwordPassword = process.env.E2E_PASSWORD_PASSWORD ?? 'password'

test.describe('Settings', () => {
  const dismissCookiesIfPresent = async (page: any) => {
    const bannerButton = page.getByRole('button', { name: /Aceptar|Accept|Rechazar|Reject/i }).first()
    if (await bannerButton.isVisible()) {
      await bannerButton.click({ force: true })
    }
  }

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

  test('profile update persists', async ({ page }) => {
    await login(page, { email: profileEmail, password: profilePassword })
    await gotoDashboardRoute(page, '/dashboard/settings')
    await dismissCookiesIfPresent(page)
    const nameInput = page.getByLabel(/nombre|name/i)
    await nameInput.fill('Profile Updated')
    await page.getByRole('button', { name: /guardar cambios|save changes/i }).click()
    await expect(page.getByText(/perfil actualizado|profile updated|perfil actualitzat/i).first()).toBeVisible()

    await page.reload()
    await expect(nameInput).toHaveValue('Profile Updated')
  })

  test('password change succeeds', async ({ page }) => {
    await login(page, { email: passwordEmail, password: passwordPassword })
    await gotoDashboardRoute(page, '/dashboard/settings/security')
    await dismissCookiesIfPresent(page)
    await page.getByPlaceholder(/Contraseña actual|Contrasenya actual|Current password/i).fill(passwordPassword)
    await page.locator('input[name="password"]').first().fill('newpassword123')
    await page.locator('input[name="password_confirmation"]').first().fill('newpassword123')
    const passwordForm = page.locator('form', {
      has: page.getByPlaceholder(/Contraseña actual|Contrasenya actual|Current password/i)
    })
    await passwordForm.getByRole('button', { name: /Actualizar|Update|Actualitza/i }).click()
    await expect(
      page.getByText(/contraseña actualizada|password updated|contrasenya actualitzada|settings\.security\.toasts\.password_updated/i).first()
    ).toBeVisible()
  })
})
