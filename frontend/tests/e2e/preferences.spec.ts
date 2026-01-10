import { test, expect } from '@playwright/test'
import { login } from './helpers/auth'

const selectOption = async (page: any, trigger: any, optionName: string) => {
  await trigger.click({ force: true })
  await page.getByRole('option', { name: optionName, exact: true }).click({ force: true })
}

const dismissCookiesIfPresent = async (page: any) => {
  const bannerButton = page.getByRole('button', { name: /Aceptar|Accept|Rechazar|Reject/i }).first()
  if (await bannerButton.isVisible()) {
    await bannerButton.click({ force: true })
  }
}

test.describe('Preferences', () => {
  test('update preferences and persist', async ({ page }) => {
    await login(page)
    await page.goto('/dashboard/settings/preferences')
    await page.getByTestId('preferences-save').waitFor()
    await dismissCookiesIfPresent(page)

    await selectOption(page, page.getByRole('combobox', { name: /Tema/i }), 'Oscuro')
    await selectOption(page, page.getByRole('combobox', { name: /Color principal/i }), 'Verde')

    await page.getByTestId('preferences-save').click()
    await expect(
      page.getByText(/Preferences updated|Preferencias actualizadas|Preferències actualitzades/i).first()
    ).toBeVisible()

    await page.reload()
    await expect(page.getByRole('combobox', { name: /Tema/i })).toContainText(/Oscuro/i)
    await expect(page.getByRole('combobox', { name: /Color principal/i })).toContainText(/Verde/i)
  })
})
