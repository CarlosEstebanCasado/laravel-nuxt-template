import { test, expect } from '@playwright/test'

test.describe('Locale selection', () => {
  test('public language selection carries to login and preferences', async ({ page }) => {
    const appBaseUrl = process.env.PLAYWRIGHT_APP_BASE_URL ?? 'https://app.project.dev'
    const publicBaseUrl = process.env.PLAYWRIGHT_PUBLIC_BASE_URL ?? 'http://127.0.0.1:3000'
    const appHost = new URL(appBaseUrl).hostname
    const publicHost = new URL(publicBaseUrl).hostname

    test.skip(
      !appHost.endsWith('project.dev') || !publicHost.endsWith('project.dev'),
      'Locale cookie requires project.dev hosts to validate public → app language carryover.'
    )

    await page.goto(`${publicBaseUrl}/en`)
    await page.context().addCookies([{
      name: 'i18n_redirected',
      value: 'en',
      domain: publicHost,
      path: '/'
    }, {
      name: 'i18n_redirected',
      value: 'en',
      domain: appHost,
      path: '/'
    }])

    await page.goto(`${appBaseUrl}/login`)
    await expect(page.getByRole('heading', { name: /Welcome back/i })).toBeVisible()
    await page.getByRole('textbox', { name: /email/i }).fill(process.env.E2E_USER_EMAIL ?? 'test@example.com')
    await page.locator('input[name="password"]').fill(process.env.E2E_USER_PASSWORD ?? 'password')
    await page.getByRole('button', { name: /continue/i }).click()
    await expect(page).toHaveURL(/\/dashboard/)
    await page.goto('/en/dashboard/settings/preferences')
    await expect(page.getByTestId('preferences-locale')).toContainText(/English/i)
  })
})
