import type { IVideoPlayer } from '../interfaces/IVideoPlayer'
import type { IYTPlayerInstance } from '../interfaces/IYTPlayerInstance'
import { createEmbedWrapper, elementorApiLoader } from './embed'

let sdkLoading = false
const readyCallbacks: Array<() => void> = []

/** Standalone loader (plugin used without Elementor): inject the IFrame API once, fan out on ready. */
function ownLoader(cb: () => void): void {
  if (window.YT?.Player) {
    cb()
    return
  }
  readyCallbacks.push(cb)
  if (sdkLoading) {
    return
  }
  sdkLoading = true
  // Chain any existing handler (e.g. from another plugin) so both fire when the API loads.
  const previous = window.onYouTubeIframeAPIReady
  window.onYouTubeIframeAPIReady = () => {
    previous?.()
    for (const c of readyCallbacks) {
      c()
    }
    readyCallbacks.length = 0
  }
  const script = document.createElement('script')
  script.src = 'https://www.youtube.com/iframe_api'
  document.head.appendChild(script)
}

function onYTReady(cb: () => void): void {
  const loader = elementorApiLoader('youtube')
  if (loader) {
    loader.onApiReady(() => cb())
    return
  }
  ownLoader(cb)
}

export class YoutubePlayer implements IVideoPlayer {
  private player: IYTPlayerInstance | null = null
  private destroyed = false
  private pendingPlay = false
  private readonly wrapper: HTMLElement

  constructor(screen: HTMLElement, videoUrl: string) {
    this.wrapper = createEmbedWrapper(screen, 'youtube')
    // YT replaces this mount node with its iframe, so the iframe lands inside our cover-sized wrapper.
    const mount = document.createElement('div')
    this.wrapper.appendChild(mount)

    onYTReady(() => {
      if (this.destroyed || !window.YT) {
        return
      }
      this.player = new window.YT.Player(mount, {
        videoId: this.extractVideoId(videoUrl),
        // No autoplay/mute/loop here: mute + playback are driven on ready, loop is manual (below).
        playerVars: {
          controls: 0,
          rel: 0,
          playsinline: 1,
          modestbranding: 1,
          fs: 0,
          iv_load_policy: 3,
          disablekb: 1,
          cc_load_policy: 0
        },
        events: {
          onReady: () => {
            this.player?.mute()
            if (this.pendingPlay) {
              this.player?.playVideo()
            }
          },
          onStateChange: (event: { data: number }) => {
            // YT's `loop` playerVar needs a `playlist`; seek-to-start on ENDED loops a single video.
            if (window.YT && event.data === window.YT.PlayerState.ENDED) {
              this.player?.seekTo(0)
              this.player?.playVideo()
            }
          },
          onError: (event: { data: number }) => {
            // 100=not found, 101/150=embedding disabled, 2=bad id. Drop the wrapper → poster fallback.
            console.warn(
              '[device-mockups-for-elementor] YouTube video could not be embedded:',
              videoUrl,
              event.data
            )
            this.player?.destroy()
            this.player = null
            this.wrapper.remove()
          }
        }
      })
    })
  }

  play(): void {
    if (!this.player) {
      this.pendingPlay = true
      return
    }
    this.player.playVideo()
    this.pendingPlay = false
  }

  pause(): void {
    this.pendingPlay = false
    this.player?.pauseVideo()
  }

  destroy(): void {
    this.destroyed = true
    this.player?.destroy()
    this.player = null
    this.wrapper.remove()
  }

  private extractVideoId(url: string): string {
    const match = url.match(/(?:v=|youtu\.be\/|embed\/|shorts\/)([^&?/]+)/)
    return match?.[1] ?? ''
  }
}
