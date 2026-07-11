export interface IApiLoader {
  onApiReady(cb: (api: unknown) => void): void
}
