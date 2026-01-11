import { test, expect } from '@playwright/test'

const publicBaseUrl = process.env.PLAYWRIGHT_PUBLIC_BASE_URL ?? 'http://127.0.0.1:3000'

test.describe('Public pages', () => {
  test('home page renders', async ({ page }) => {
    await page.goto(publicBaseUrl)
    await expect(page.getByText(/ship your/i)).toBeVisible()
  })

  test('pricing page renders', async ({ page }) => {
    await page.goto(`${publicBaseUrl}/pricing`)
    await expect(page).toHaveURL(/pricing/)
  })

  test('docs index redirects', async ({ page }) => {
    await page.goto(`${publicBaseUrl}/docs`)
    await expect(page).toHaveURL(/\/docs\/getting-started/)
  })

  test('blog index renders', async ({ page }) => {
    await page.goto(`${publicBaseUrl}/blog`)
    await expect(page).toHaveURL(/blog/)
  })
})
