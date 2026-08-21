import type { IApiLoader } from '../interfaces/IApiLoader'

const EMBED_CLASS = 'arts-device-mockup__video-embed'

/**
 * Create the wrapper the external players inject their iframe into. Kept separate from the poster
 * `.arts-device-mockup__media` so the iframe is cover-sized + clipped by CSS and torn down on destroy
 * without touching the server-rendered fallback poster.
 */
export function createEmbedWrapper(
  screen: HTMLElement,
  provider: 'youtube' | 'vimeo'
): HTMLElement {
  const wrapper = document.createElement('div')
  // The provider modifier lets CSS overscan YouTube (which can't be made chromeless) without
  // cropping Vimeo (whose background mode is already chromeless).
  wrapper.className = `${EMBED_CLASS} ${EMBED_CLASS}_${provider}`
  screen.appendChild(wrapper)
  return wrapper
}

/**
 * Elementor's shared YT/Vimeo API loaders (single script inject + ready poll) — available only once
 * Elementor has run init(); `utils` does not exist before that. The document-wide pass in index.ts
 * always beats Elementor's init, so mockups on the initial render self-inject the SDK; only later
 * inits (AJAX-injected content, editor re-renders) reuse this.
 */
export function elementorApiLoader(provider: 'youtube' | 'vimeo'): IApiLoader | undefined {
  const utils = (
    window.elementorFrontend as { utils?: Record<string, IApiLoader | undefined> } | undefined
  )?.utils
  const loader = utils?.[provider]
  return loader && typeof loader.onApiReady === 'function' ? loader : undefined
}
