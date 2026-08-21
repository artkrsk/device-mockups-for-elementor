import type { IVimeoPlayerInstance } from './IVimeoPlayerInstance'

export interface IVimeoNamespace {
  Player: new (el: Element, opts: Record<string, unknown>) => IVimeoPlayerInstance
}
