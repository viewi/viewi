#!/usr/bin/env node
import * as esbuild from 'esbuild'
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

await esbuild.build({ ...base, outfile: './dist/viewi.js' })
await esbuild.build({ ...base, minify: true, outfile: './dist/viewi.min.js' })
