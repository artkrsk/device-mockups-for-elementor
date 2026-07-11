export interface IVimeoPlayerInstance {
  play(): Promise<void>
  pause(): Promise<void>
  ready(): Promise<void>
  destroy(): Promise<void>
}
