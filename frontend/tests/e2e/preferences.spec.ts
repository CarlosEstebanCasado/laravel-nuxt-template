import { test, expect } from '@playwright/test'
import { login } from './helpers/auth'

const preferencesEmail = process.env.E2E_PREFERENCES_EMAIL ?? 'preferencesuser@example.com'
const preferencesPassword = process.env.E2E_PREFERENCES_PASSWORD ?? 'password'

const dismissCookiesIfPresent = async (page: any) => {
  const bannerButton = page.getByRole('button', { name: /Aceptar|Accept|Rechazar|Reject/i }).first()
  if (await bannerButton.isVisible()) {
    await bannerButton.click({ force: true })
  }
}

test.describe('Preferences', () => {
  test('preferences reflect seeded values', async ({ page }) => {
    await login(page, { email: preferencesEmail, password: preferencesPassword })
    await page.goto('/dashboard/settings/preferences')
    await page.getByTestId('preferences-save').waitFor()
    await dismissCookiesIfPresent(page)

    await expect(page.getByTestId('preferences-theme')).toContainText(/oscuro|dark|fosc/i)
    await expect(page.getByTestId('preferences-primary-color')).toContainText(/verde|green|verd/i)
    await expect(page.getByTestId('preferences-neutral-color')).toContainText(/pizarra|slate|pissarra/i)
    await expect(page.getByTestId('preferences-timezone')).toContainText(/Europe\/Madrid/i)
  })
})
