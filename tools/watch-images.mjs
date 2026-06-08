import { spawn } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import chokidar from 'chokidar';

const rootDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const imagesDir = path.join(rootDir, 'assets', 'images');
const imagePattern = /\.(jpe?g|png)$/i;
let timer = null;
let running = false;
let pending = false;

function runOptimizer(reason) {
  if (running) {
    pending = true;
    return;
  }

  running = true;
  console.log(`optimize:images triggered by ${reason}`);

  const command = process.platform === 'win32' ? 'npm.cmd' : 'npm';
  const child = spawn(command, ['run', 'optimize:images'], {
    cwd: rootDir,
    stdio: 'inherit',
  });

  child.on('exit', (code) => {
    running = false;

    if (code !== 0) {
      console.error(`optimize:images exited with code ${code}`);
    }

    if (pending) {
      pending = false;
      runOptimizer('pending changes');
    }
  });
}

function schedule(reason) {
  clearTimeout(timer);
  timer = setTimeout(() => runOptimizer(reason), 350);
}

const watcher = chokidar.watch(imagesDir, {
  ignoreInitial: true,
  ignored: [
    /(^|[/\\])\../,
    /[/\\]node_modules[/\\]/,
  ],
  awaitWriteFinish: {
    stabilityThreshold: 600,
    pollInterval: 100,
  },
});

watcher
  .on('add', (filePath) => {
    if (imagePattern.test(filePath)) {
      schedule(`new image ${path.relative(rootDir, filePath)}`);
    }
  })
  .on('change', (filePath) => {
    if (imagePattern.test(filePath)) {
      schedule(`changed image ${path.relative(rootDir, filePath)}`);
    }
  })
  .on('ready', () => {
    console.log(`Watching ${path.relative(rootDir, imagesDir)} for jpg/jpeg/png changes...`);
  });
