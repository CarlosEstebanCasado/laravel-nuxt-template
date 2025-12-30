import { promises as fs } from 'node:fs'
import { join } from 'pathe'
import { defineNuxtModule } from '@nuxt/kit'

export default defineNuxtModule({
  meta: {
    name: 'ensure-nuxt-image-binaries'
  },
  setup(_, nuxt) {
    nuxt.hook('nitro:init', (nitro) => {
      nitro.hooks.hook('compiled', async () => {
        const sourceDir = join(nuxt.options.rootDir, 'node_modules', '@img')
        const targetDir = join(nitro.options.output.serverDir, 'node_modules', '@img')

        try {
          await fs.cp(sourceDir, targetDir, { recursive: true })
        } catch {
          // Best-effort: if node_modules/@img is missing, Nuxt Image will warn anyway.
        }
      })
    })
  }
})
