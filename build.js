import * as esbuild from 'esbuild';
import { glob } from 'glob';
import path from 'path';
import fs from 'fs';

const SOURCE_DIR = 'resources/js';
const OUTPUT_DIR = 'public/js';

// Kiểm tra mode từ argument
const isDev = process.argv.includes('--dev');

/**
 * Build tất cả JS files và giữ nguyên cấu trúc thư mục
 */
async function build() {
  const mode = isDev ? 'DEVELOPMENT' : 'PRODUCTION';
  console.log(`🔨 Building JavaScript files... [${mode}]\n`);

  // Tìm tất cả .js files trong js/
  const files = await glob(`${SOURCE_DIR}/**/*.js`);

  if (files.length === 0) {
    console.log('⚠️  No JavaScript files found!');
    return;
  }

  console.log(`📦 Found ${files.length} files to build:\n`);

  // Cấu hình theo mode
  const buildOptions = isDev
    ? {
        // DEVELOPMENT: giữ debugger, không minify, có sourcemap
        minify: false,
        sourcemap: 'inline',
        keepNames: true,
        drop: [],
      }
    : {
        // PRODUCTION: minify, xóa debugger + console, không sourcemap
        minify: true,
        sourcemap: false,
        keepNames: false,
        drop: ['debugger', 'console'],
      };

  // Build từng file
  for (const file of files) {
    try {
      const relativePath = path.relative(SOURCE_DIR, file);
      const parsedPath = path.parse(relativePath);
      const outputPath = path.join(
        OUTPUT_DIR,
        parsedPath.dir,
        `${parsedPath.name}.min.js`
      );

      // Tạo thư mục output nếu chưa có
      const outputDir = path.dirname(outputPath);
      if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, { recursive: true });
      }

      // Xóa file .map cũ nếu build production
      if (!isDev) {
        const mapFile = outputPath + '.map';
        if (fs.existsSync(mapFile)) {
          fs.unlinkSync(mapFile);
        }
      }

      // Build file
      await esbuild.build({
        entryPoints: [file],
        bundle: true,
        outfile: outputPath,
        format: 'iife',
        globalName: getGlobalName(relativePath),
        external: ['jquery', 'kendo'],
        // Spread build options theo mode
        ...buildOptions,
      });

      console.log(`✅ ${file} → ${outputPath}`);
    } catch (error) {
      console.error(`❌ Error building ${file}:`, error.message);
    }
  }

  console.log(`\n🎉 Build completed! [${mode}]`);
  
  if (isDev) {
    console.log('   ℹ️  debugger statements: kept');
    console.log('   ℹ️  console.log: kept');
    console.log('   ℹ️  sourcemap: inline');
  } else {
    console.log('   ℹ️  debugger statements: removed');
    console.log('   ℹ️  console.log: removed');
    console.log('   ℹ️  sourcemap: none');
    console.log('   ℹ️  minified: yes');
  }
}

/**
 * Tạo global name từ file path
 * pages/dashboard.js → PagesDashboard
 * admin/users.js → AdminUsers
 */
function getGlobalName(filePath) {
  const parts = filePath.replace('.js', '').split(path.sep);
  return parts
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');
}

// Run build
build().catch(console.error);