#!/usr/bin/env node
import * as esbuild from 'esbuild'
import { lazyGroups } from './app/lazyGroups.mjs';
let watch = false;
process.argv.forEach(function (val, index, array) {
  switch (val) {
    case '--watch': {
      watch = true;
      break;
    }
    default:
      break; // nop
  }
});

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
