import type { IYTPlayerInstance } from './IYTPlayerInstance'

export interface IYTNamespace {
  Player: new (el: Element, opts: Record<string, unknown>) => IYTPlayerInstance
  PlayerState: { ENDED: number }
}
