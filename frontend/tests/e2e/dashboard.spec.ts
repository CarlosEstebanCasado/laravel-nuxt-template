import { test, expect, type Page } from '@playwright/test'
import { login } from './helpers/auth'

const gotoDashboardRoute = async (page: Page, path: string) => {
  let loaded = false
  for (let attempt = 0; attempt < 3; attempt += 1) {
    await page.goto(path)
    await page.waitForTimeout(500)
    if (await page.getByText(/Failed to fetch dynamically imported module/i).count()) {
      await page.reload()
      continue
    }
    loaded = true
    break
  }
  if (!loaded) {
    throw new Error('No se pudo cargar la ruta del dashboard tras varios intentos.')
  }
}

test.describe('Dashboard basics', () => {
  test('preferences page loads', async ({ page }) => {
    await login(page)
    await gotoDashboardRoute(page, '/dashboard/settings/preferences')
    await expect(
      page.locator('form button[type="submit"]')
    ).toBeVisible()
  })

  test('sessions list loads', async ({ page }) => {
    await login(page)
    await gotoDashboardRoute(page, '/dashboard/settings/security')
    await expect(
      page.getByRole('button', { name: /Cerrar otras sesiones|Revoke other sessions|Close other sessions/i })
    ).toBeVisible()
  })

  test('revoke other sessions', async ({ page, browser }) => {
    await login(page)

    const otherContext = await browser.newContext()
    const otherPage = await otherContext.newPage()
    await login(otherPage)

    await gotoDashboardRoute(page, '/dashboard/settings/security')
    await page.getByRole('button', { name: /Cerrar otras sesiones|Close other sessions|Tancar altres sessions/i }).click()
    await page.getByRole('button', { name: /Cerrar otras sesiones|Close other sessions|Tancar altres sessions/i }).last().click()

    await expect(
      page.getByRole('alert').filter({ hasText: /Sesiones actualizadas|Sessions updated|Sessions actualitzades/i }).first()
    ).toBeVisible()

    await otherContext.close()
  })
})
