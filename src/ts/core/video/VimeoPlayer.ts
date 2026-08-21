import type { IVideoPlayer } from '../interfaces/IVideoPlayer'
import type { IVimeoPlayerInstance } from '../interfaces/IVimeoPlayerInstance'
import { createEmbedWrapper, elementorApiLoader } from './embed'

let sdkLoading = false
const readyCallbacks: Array<() => void> = []

/** Standalone loader (package used without Elementor): inject the Player SDK once, fan out on load. */
function ownLoader(cb: () => void): void {
  if (window.Vimeo) {
    cb()
    return
  }
  readyCallbacks.push(cb)
  if (sdkLoading) {
    return
  }
  sdkLoading = true
  const script = document.createElement('script')
  script.src = 'https://player.vimeo.com/api/player.js'
  script.onload = () => {
    for (const c of readyCallbacks) {
      c()
    }
    readyCallbacks.length = 0
  }
  document.head.appendChild(script)
}

function onVimeoReady(cb: () => void): void {
  const loader = elementorApiLoader('vimeo')
  if (loader) {
    loader.onApiReady(() => cb())
    return
  }
  ownLoader(cb)
}

export class VimeoPlayer implements IVideoPlayer {
  private player: IVimeoPlayerInstance | null = null
  private destroyed = false
  private pendingPlay = false
  private readonly wrapper: HTMLElement

  constructor(screen: HTMLElement, videoUrl: string) {
    this.wrapper = createEmbedWrapper(screen, 'vimeo')

    onVimeoReady(() => {
      if (this.destroyed || !window.Vimeo) {
        return
      }
      // `background: true` gives the chromeless muted player; Vimeo builds its iframe inside the wrapper.
      this.player = new window.Vimeo.Player(this.wrapper, {
        url: videoUrl,
        autoplay: false,
        muted: true,
        loop: true,
        controls: false,
        background: true,
        transparent: true
      })
      this.player
        .ready()
        .then(() => {
          if (this.destroyed) {
            return
          }
          // Background mode autoplays; force the resting state to match what was requested.
          if (this.pendingPlay) {
            this.play()
          } else {
            this.player?.pause().catch(() => {})
          }
        })
        .catch((error) => {
          if (this.destroyed) {
            return
          }
          // Vimeo rejects private / not-found / domain-restricted videos here and builds no iframe.
          // Drop the empty wrapper so the server-rendered poster/image shows as the fallback.
          console.warn(
            '[device-mockups-for-elementor] Vimeo video could not be embedded:',
            videoUrl,
            error
          )
          this.player = null
          this.wrapper.remove()
        })
    })
  }

  play(): void {
    if (!this.player) {
      this.pendingPlay = true
      return
    }
    this.player.play().catch(() => {})
    this.pendingPlay = false
  }

  pause(): void {
    this.pendingPlay = false
    this.player?.pause().catch(() => {})
  }

  destroy(): void {
    this.destroyed = true
    this.player?.destroy().catch(() => {})
    this.player = null
    this.wrapper.remove()
  }
}
