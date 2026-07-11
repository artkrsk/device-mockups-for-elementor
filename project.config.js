import process from 'node:process'

export default {
  slug: 'device-mockups-for-elementor',
  entry: { ts: './src/ts/index.ts', sass: './src/styles/index.sass' },
  paths: { php: './src/php', plugin: './src/wordpress-plugin', dist: './dist' },
  // Machine-specific: the Local site's plugin dir, from the gitignored .env (DEV_TARGET)
  devTarget: process.env.DEV_TARGET ?? null,
  esbuildTarget: 'es2018',
  versionConstant: 'ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION',
  vendor: { autoloaderOnly: true }
}
