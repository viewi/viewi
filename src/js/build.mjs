#!/usr/bin/env node
import * as esbuild from 'esbuild'
import * as chokidar from 'chokidar';
import anymatch from 'anymatch';
import path from 'path';
import { exec } from 'node:child_process';
import { lazyGroups } from './app/lazyGroups.mjs';
let watchMode = false;

process.argv.forEach(function (val, index, array) {
  switch (val) {
    case '--watch': {
      watchMode = true;
      break;
    }
    default:
      break; // noop
  }
});

const runBuild = async function () {
  const base = {
    entryPoints: ['./viewi/index.ts'],
    logLevel: "info",
    treeShaking: true,
    bundle: true,
  };

  await esbuild.build({ ...base, outfile: './dist/viewi.js' });
  await esbuild.build({ ...base, minify: true, outfile: './dist/viewi.min.js' });

  for (let group in lazyGroups) {
    const entry = lazyGroups[group];
    await esbuild.build({ ...base, entryPoints: [entry], outfile: `./dist/viewi.${group}.js` });
    await esbuild.build({ ...base, entryPoints: [entry], minify: true, outfile: `./dist/viewi.${group}.min.js` });
  }
};

const runServerBuild = function () {
  // exec php viewi build
  return new Promise(function (resolve, reject) {
    exec('php ./../build.php', (error, stdout, stderr) => {
      if (error) {
        reject(`exec error: ${error} \n${stdout}${stderr}`);
        return;
      }
      console.log(`PHP build: ${stdout}`);
      if (stderr) {
        console.error(`PHP build error: ${stderr}`);
      }
      resolve();
    });
  });
};

const runBuildAll = async function () {
  console.log('Running build..');
  try {
    await runServerBuild();
  } catch (ex) {
    console.error(ex);
  }
  // await runBuild();
};

let buildTimer = 0;

const runWatch = async function () {
  const ignored = ['**/build/**', '/js/dist/**', '/js/viewi/**', '**/node_modules/**', '**/app/**'];
  // https://github.com/paulmillr/chokidar
  chokidar.watch(['.\\..\\']).on('all', (event, itemPath) => {
    const normalizedPath = path.normalize(itemPath).replace(/\\/g, '/').replace('..', '');
    // https://www.npmjs.com/package/anymatch
    if (!anymatch(ignored, normalizedPath)) {
      // console.log(event, normalizedPath);
      if (buildTimer) {
        clearTimeout(buildTimer);
      }
      buildTimer = setTimeout(runBuildAll, 200);
    }
  });
};

if (watchMode) {
  runWatch();
} else {
  runBuild();
}
