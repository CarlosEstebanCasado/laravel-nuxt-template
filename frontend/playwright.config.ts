import { defineConfig, devices } from '@playwright/test'

const shouldStartServer = process.env.PLAYWRIGHT_WEB_SERVER === 'true'
const appBaseUrl = process.env.PLAYWRIGHT_APP_BASE_URL
  ?? (shouldStartServer ? 'http://localhost:3000' : 'https://app.project.dev')

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60_000,
  workers: process.env.PLAYWRIGHT_WORKERS ? Number(process.env.PLAYWRIGHT_WORKERS) : 1,
  expect: {
    timeout: 10_000,
  },
  use: {
    baseURL: appBaseUrl,
    ignoreHTTPSErrors: true,
    trace: 'on-first-retry',
  },
  webServer: shouldStartServer
    ? {
        command: 'npm run dev -- --host 0.0.0.0 --port 3000',
        url: appBaseUrl,
        reuseExistingServer: true,
      }
    : undefined,
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
})
