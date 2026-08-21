import type { IVideoPlayer } from '../interfaces/IVideoPlayer'

export class HostedPlayer implements IVideoPlayer {
  private readonly video: HTMLVideoElement

  constructor(video: HTMLVideoElement) {
    this.video = video
  }

  play(): void {
    // Browser autoplay restrictions silently reject play() before user interaction on mobile —
    // swallowing the rejection here is intentional; the video stays paused until unblocked.
    this.video.play().catch(() => {})
  }

  pause(): void {
    this.video.pause()
  }

  destroy(): void {}
}
