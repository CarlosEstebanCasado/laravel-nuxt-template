export interface OgImageOptions {
  [key: string]: unknown
}

export interface OgImageComponentOptions extends OgImageOptions {
  component?: string
}

export function defineOgImage(_options: OgImageOptions = {}) {
  if (import.meta.dev) {
    console.warn('`defineOgImage()` is skipped because OG image generation is disabled in SPA mode.')
  }
}

export function defineOgImageComponent(
  _component: string,
  _props: Record<string, unknown> = {},
  _options: OgImageComponentOptions = {}
) {
  if (import.meta.dev) {
    console.warn('`defineOgImageComponent()` is skipped because OG image generation is disabled in SPA mode.')
  }
}

export function defineOgImageScreenshot(_options: OgImageOptions = {}) {
  if (import.meta.dev) {
    console.warn('`defineOgImageScreenshot()` is skipped because OG image generation is disabled in SPA mode.')
  }
}
