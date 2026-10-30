import type { IVideoPlayer } from '../interfaces/IVideoPlayer'

export class HostedPlayer implements IVideoPlayer {
  private readonly video: HTMLVideoElement

  constructor(video: HTMLVideoElement) {
    this.video = video
  }

  play(): void {
    // The browser autoplay policy can reject play() (NotAllowedError) before any user interaction —
    // swallowing the rejection here is intentional; the video stays paused until unblocked.
    this.video.play().catch(() => {})
  }

  pause(): void {
    this.video.pause()
  }

  destroy(): void {}
}
