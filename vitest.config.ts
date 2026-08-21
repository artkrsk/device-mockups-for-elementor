import { createVitestConfig } from '@arts/wp-plugin-tooling/vitest'
import { defineConfig } from 'vitest/config'

// Shared shape; no tests yet — the suite gate starts passing (passWithNoTests)
// and tightens itself the moment the first test lands.
export default defineConfig(
  createVitestConfig({ defineKey: '__ARTS_DEVICE_MOCKUPS_VERSION__', setupFiles: [] })
)
