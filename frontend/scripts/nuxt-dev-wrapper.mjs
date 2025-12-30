import { spawn } from 'node:child_process'
import { promises as fs } from 'node:fs'
import { join } from 'pathe'

const rootDir = process.cwd()
const rootVueDir = join(rootDir, 'node_modules', '@vue')
const devVueDir = join(rootDir, '.nuxt', 'dev', 'node_modules', '@vue')

const forwardArgs = process.argv.slice(2)

const startNuxt = () =>
  spawn('npx', ['nuxt', 'dev', ...forwardArgs], {
    stdio: 'inherit',
    shell: false
  })

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

const syncVueCjsProd = async () => {
  let packages = []

  try {
    packages = await fs.readdir(rootVueDir, { withFileTypes: true })
  } catch {
    return
  }

  for (const entry of packages) {
    if (!entry.isDirectory()) {
      continue
    }

    const rootDist = join(rootVueDir, entry.name, 'dist')
    const devDist = join(devVueDir, entry.name, 'dist')

    let files = []
    try {
      files = await fs.readdir(rootDist)
    } catch {
      continue
    }

    const prodFiles = files.filter((file) => file.endsWith('.cjs.prod.js'))
    if (prodFiles.length === 0) {
      continue
    }

    await fs.mkdir(devDist, { recursive: true })
    for (const file of prodFiles) {
      try {
        await fs.copyFile(join(rootDist, file), join(devDist, file))
      } catch {
        // Best-effort: ignore missing files to avoid blocking dev server.
      }
    }
  }
}

const keepEnsuringDevShared = async (durationMs = 15000) => {
  const start = Date.now()

  while (Date.now() - start < durationMs) {
    await syncVueCjsProd()

    await sleep(250)
  }
}

const run = async () => {
  await syncVueCjsProd()

  let child = startNuxt()
  let restarted = false

  const attachExitHandler = (processRef) => {
    processRef.on('exit', async (code) => {
      await keepEnsuringDevShared()

      if (!restarted) {
        restarted = true
        await syncVueCjsProd()
        child = startNuxt()
        attachExitHandler(child)
        keepEnsuringDevShared()
        return
      }

      process.exit(code ?? 0)
    })
  }

  attachExitHandler(child)
}

await run()
