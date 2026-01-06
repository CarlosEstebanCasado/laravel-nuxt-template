import { test, expect, type Page } from '@playwright/test'
import { login } from './helpers/auth'

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
})
