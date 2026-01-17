import { randomBytes } from 'node:crypto'

import { setHeader } from 'h3'
import { defineNitroPlugin } from 'nitropack/runtime'

import { useRuntimeConfig } from '#imports'

const toOrigin = (value: string | undefined): string | null => {
  if (!value) {
    return null
  }

  try {
    return new URL(value).origin
  } catch {
    return null
  }
}

const applyNonce = (input: string, nonce: string): string =>
  input
    .replace(/<script(?![^>]*\snonce=)([^>]*)>/gi, `<script$1 nonce="${nonce}">`)
    .replace(/<style(?![^>]*\snonce=)([^>]*)>/gi, `<style$1 nonce="${nonce}">`)

export default defineNitroPlugin((nitroApp) => {
  nitroApp.hooks.hook('render:html', (html, { event }) => {
    const nonce = randomBytes(16).toString('base64')
    const config = useRuntimeConfig()
    const apiOrigin = toOrigin(config.public.apiBase)

    event.context.cspNonce = nonce

    html.head = html.head.map((chunk) => applyNonce(chunk, nonce))
    html.body = html.body.map((chunk) => applyNonce(chunk, nonce))
    html.bodyPrepend = html.bodyPrepend.map((chunk) => applyNonce(chunk, nonce))
    html.bodyAppend = html.bodyAppend.map((chunk) => applyNonce(chunk, nonce))

    const connectSrc = [
      "'self'",
      apiOrigin,
      'https://api.iconify.design',
      'ws:',
      'wss:'
    ].filter(Boolean)

    const csp = [
      "default-src 'self'",
      `script-src 'self' 'nonce-${nonce}'`,
      "style-src 'self' 'unsafe-inline'",
      "img-src 'self' data: https:",
      "font-src 'self' data: https:",
      `connect-src ${connectSrc.join(' ')}`,
      "media-src 'self' https://res.cloudinary.com",
      "frame-ancestors 'self'",
      "base-uri 'self'",
      "object-src 'none'"
    ].join('; ')

    const reportOnly = [
      "default-src 'self'",
      `script-src 'self' 'nonce-${nonce}'`,
      "style-src 'self'",
      "img-src 'self' data: https:",
      "font-src 'self' data: https:",
      `connect-src ${connectSrc.join(' ')}`,
      "media-src 'self' https://res.cloudinary.com",
      "frame-ancestors 'self'",
      "base-uri 'self'",
      "object-src 'none'"
    ].join('; ')

    setHeader(event, 'Content-Security-Policy', csp)
    setHeader(event, 'Content-Security-Policy-Report-Only', reportOnly)
  })
})
