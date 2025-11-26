import * as esbuild from 'esbuild';
import { glob } from 'glob';
import path from 'path';
import fs from 'fs';
import chokidar from 'chokidar';

const SOURCE_DIR = 'resources/js';
const OUTPUT_DIR = 'public/js';

/**
 * Build một file
 */
async function buildFile(file) {
  try {
    const relativePath = path.relative(SOURCE_DIR, file);
    const parsedPath = path.parse(relativePath);
    const outputPath = path.join(
      OUTPUT_DIR,
      parsedPath.dir,
      `${parsedPath.name}.min.js`
    );

    // Tạo thư mục output
    const outputDir = path.dirname(outputPath);
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }

    // Build
    await esbuild.build({
      entryPoints: [file],
      bundle: true,
      minify: true,
      sourcemap: true,
      outfile: outputPath,
      format: 'iife',
      globalName: getGlobalName(relativePath),
      external: ['jquery', 'kendo'],
    });

    const timestamp = new Date().toLocaleTimeString();
    console.log(`[${timestamp}] ✅ Built: ${relativePath}`);
  } catch (error) {
    console.error(`❌ Error building ${file}:`, error.message);
  }
}

/**
 * Build tất cả files
 */
async function buildAll() {
  console.log('🔨 Building all files...\n');
  const files = await glob(`${SOURCE_DIR}/**/*.js`);
  
  for (const file of files) {
    await buildFile(file);
  }
  
  console.log('\n✅ Initial build completed!\n');
}

/**
 * Tạo global name
 */
function getGlobalName(filePath) {
  const parts = filePath.replace('.js', '').split(path.sep);
  return parts
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');
}

/**
 * Watch mode
 */
async function watch() {
  // Build tất cả lần đầu
  await buildAll();

  console.log('👀 Watching for changes...\n');

  // Watch thư mục js/
  const watcher = chokidar.watch(`${SOURCE_DIR}/**/*.js`, {
    persistent: true,
    ignoreInitial: true,
  });

  // Khi file thay đổi
  watcher.on('change', async (file) => {
    console.log(`📝 File changed: ${file}`);
    await buildFile(file);
  });

  // Khi file mới được tạo
  watcher.on('add', async (file) => {
    console.log(`➕ File added: ${file}`);
    await buildFile(file);
  });

  // Khi file bị xóa
  watcher.on('unlink', (file) => {
    const relativePath = path.relative(SOURCE_DIR, file);
    const parsedPath = path.parse(relativePath);
    const outputPath = path.join(
      OUTPUT_DIR,
      parsedPath.dir,
      `${parsedPath.name}.min.js`
    );
    
    if (fs.existsSync(outputPath)) {
      fs.unlinkSync(outputPath);
      console.log(`🗑️  Deleted: ${outputPath}`);
    }
  });
}

// Run watch
watch().catch(console.error);