import { expect, type Page } from '@playwright/test'

const defaultEmail = process.env.E2E_USER_EMAIL ?? 'test@example.com'
const defaultPassword = process.env.E2E_USER_PASSWORD ?? 'password'

export const login = async (
  page: Page,
  credentials?: { email?: string; password?: string }
) => {
  const email = credentials?.email ?? defaultEmail
  const password = credentials?.password ?? defaultPassword
  await page.goto('/login')
  await page.getByRole('textbox', { name: /email/i }).fill(email)
  await page.locator('input[name="password"]').fill(password)
  await page.getByRole('button', { name: /continuar|continue|iniciar|acceder/i }).click()
  await expect(page).toHaveURL(/\/dashboard/)
}

export const logout = async (page: Page) => {
  await page.getByTestId('user-menu-trigger').click()

  await page.getByRole('menuitem', { name: /cerrar sesión|logout/i }).click()
  await expect(page).toHaveURL(/\/login/)
}
