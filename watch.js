import * as esbuild from 'esbuild';
import { glob } from 'glob';
import path from 'path';
import fs from 'fs';

const SOURCE_DIR = 'resources/js';
const OUTPUT_DIR = 'public/js';

async function watch() {
  console.log('🔨 Starting esbuild watch mode (DEVELOPMENT)...\n');

  // Tìm tất cả entry points
  const files = await glob(`${SOURCE_DIR}/**/*.js`);
  
  // Build entries
  const entryPoints = files.map(file => {
    const relativePath = path.relative(SOURCE_DIR, file);
    const parsedPath = path.parse(relativePath);
    return {
      in: file,
      out: path.join(parsedPath.dir, `${parsedPath.name}.min`),
    };
  });

  // Tạo output dir
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  }

  try {
    // Tạo esbuild context
    const ctx = await esbuild.context({
      entryPoints: entryPoints,
      bundle: true,
      minify: false,
      sourcemap: 'inline',
      outdir: OUTPUT_DIR,
      format: 'iife',
      external: ['jquery', 'kendo'],
      keepNames: true,
      logLevel: 'info',
      // Plugin để log rebuild
      plugins: [{
        name: 'rebuild-notify',
        setup(build) {
          build.onEnd(result => {
            const timestamp = new Date().toLocaleTimeString();
            if (result.errors.length > 0) {
              console.log(`[${timestamp}] ❌ Build failed with ${result.errors.length} errors`);
            } else {
              console.log(`[${timestamp}] ✅ Build completed`);
            }
          });
        }
      }]
    });

    // BẬT WATCH MODE - esbuild tự xử lý file watching
    await ctx.watch();

    console.log('👀 Watching for changes... Press Ctrl+C to stop.\n');

    // Graceful shutdown
    process.on('SIGINT', async () => {
      console.log('\n🛑 Stopping watcher...');
      await ctx.dispose();
      process.exit(0);
    });

    // Giữ process chạy
    await new Promise(() => {});
    
  } catch (error) {
    console.error('❌ Error:', error);
    process.exit(1);
  }
}

watch();