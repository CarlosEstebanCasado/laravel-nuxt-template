import { promises as fs } from 'node:fs'
import { join } from 'pathe'

const rootDir = process.cwd()
const devDir = join(rootDir, '.nuxt', 'dev')
const devNodeModules = join(devDir, 'node_modules')
const devSharedDir = join(devNodeModules, '@vue', 'shared', 'dist')
const devSharedProd = join(devSharedDir, 'shared.cjs.prod.js')
const devShared = join(devSharedDir, 'shared.cjs.js')
const rootSharedDir = join(rootDir, 'node_modules', '@vue', 'shared', 'dist')
const rootSharedProd = join(rootSharedDir, 'shared.cjs.prod.js')
const rootShared = join(rootSharedDir, 'shared.cjs.js')

const ensureSharedFiles = async () => {
  await fs.mkdir(devSharedDir, { recursive: true })

  try {
    await fs.copyFile(rootShared, devShared)
    await fs.copyFile(rootSharedProd, devSharedProd)
  } catch {
    // Best-effort: if root files are missing, Nuxt will still error.
  }
}

await ensureSharedFiles()
