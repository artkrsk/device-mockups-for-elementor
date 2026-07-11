export interface IYTPlayerInstance {
  playVideo(): void
  pauseVideo(): void
  mute(): void
  seekTo(seconds: number, allowSeekAhead?: boolean): void
  destroy(): void
}
