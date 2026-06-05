import { readdir, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const rootDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const imagesDir = path.join(rootDir, 'assets', 'images');
const args = new Set(process.argv.slice(2));
const dryRun = args.has('--dry-run');
const force = args.has('--force');

const getNumberArg = (prefix, fallback) => {
  const raw = process.argv.slice(2).find((arg) => arg.startsWith(prefix));
  if (!raw) return fallback;

  const value = Number(raw.slice(prefix.length));
  return Number.isFinite(value) && value > 0 ? value : fallback;
};

const thresholdKb = getNumberArg('--threshold=', 150);
const quality = Math.min(100, Math.max(1, getNumberArg('--quality=', 78)));
const minBytes = thresholdKb * 1024;
const eligibleExtensions = new Set(['.jpg', '.jpeg', '.png']);

async function listFiles(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const absolutePath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      files.push(...await listFiles(absolutePath));
    } else {
      files.push(absolutePath);
    }
  }

  return files;
}

function toRelative(filePath) {
  return path.relative(rootDir, filePath).replaceAll(path.sep, '/');
}

async function shouldWrite(sourcePath, targetPath) {
  if (force) return true;

  try {
    const [sourceInfo, targetInfo] = await Promise.all([stat(sourcePath), stat(targetPath)]);
    return targetInfo.mtimeMs < sourceInfo.mtimeMs;
  } catch {
    return true;
  }
}

async function convertImage(sourcePath, targetPath, options = {}) {
  const shouldConvert = await shouldWrite(sourcePath, targetPath);

  if (!shouldConvert) {
    return { status: 'skipped-current', targetPath };
  }

  if (dryRun) {
    return { status: 'dry-run', targetPath };
  }

  let pipeline = sharp(sourcePath).rotate();

  if (options.width) {
    pipeline = pipeline.resize({ width: options.width, withoutEnlargement: true });
  }

  await pipeline.webp({ quality, effort: 5 }).toFile(targetPath);
  return { status: 'converted', targetPath };
}

const allFiles = await listFiles(imagesDir);
const eligibleFiles = [];

for (const filePath of allFiles) {
  const ext = path.extname(filePath).toLowerCase();

  if (!eligibleExtensions.has(ext)) {
    continue;
  }

  const fileInfo = await stat(filePath);

  if (fileInfo.size < minBytes) {
    continue;
  }

  eligibleFiles.push({ filePath, size: fileInfo.size });
}

let converted = 0;
let skipped = 0;
let dryRuns = 0;

for (const { filePath, size } of eligibleFiles) {
  const targetPath = filePath.replace(/\.(jpe?g|png)$/i, '.webp');
  const result = await convertImage(filePath, targetPath);
  const sizeKb = Math.round(size / 1024);

  if (result.status === 'converted') converted += 1;
  if (result.status === 'skipped-current') skipped += 1;
  if (result.status === 'dry-run') dryRuns += 1;

  console.log(`${result.status}: ${toRelative(filePath)} (${sizeKb} KB) -> ${toRelative(targetPath)}`);
}

const heroBackgroundCandidates = [
  path.join(imagesDir, 'creamod5.jpg'),
  path.join(imagesDir, 'creamod5.jpeg'),
];
const heroBackgroundLarge = path.join(imagesDir, 'creamod5-1800.webp');
let heroBackground = '';

for (const candidate of heroBackgroundCandidates) {
  try {
    await stat(candidate);
    heroBackground = candidate;
    break;
  } catch {
    // Try the next supported original extension.
  }
}

if (heroBackground) {
  const result = await convertImage(heroBackground, heroBackgroundLarge, { width: 1800 });

  if (result.status === 'converted') converted += 1;
  if (result.status === 'skipped-current') skipped += 1;
  if (result.status === 'dry-run') dryRuns += 1;

  console.log(`${result.status}: ${toRelative(heroBackground)} -> ${toRelative(heroBackgroundLarge)} (1800px)`);
} else {
  console.warn('warning: assets/images/creamod5.jpg or creamod5.jpeg not found; skipped 1800px background variant');
}

console.log(`summary: ${converted} converted, ${dryRuns} dry-run, ${skipped} skipped-current, ${eligibleFiles.length} eligible sources`);
