// project-root/lib/cleanupUtils.js

const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const glob = require("glob");

const ignoreDirs = ["node_modules", "dist", "build", ".git"];

function getAllFiles(
  projectRoot,
  extensions = ["js", "jsx", "ts", "tsx", "css", "json", "vue", "html"]
) {
  return glob.sync(`${projectRoot}/**/*.{${extensions.join(",")}}`, {
    ignore: ignoreDirs.map((dir) => `${dir}/**`),
  });
}

function getFileHash(filePath) {
  const content = fs.readFileSync(filePath, "utf-8");
  return crypto.createHash("md5").update(content).digest("hex");
}

function findDuplicates(files) {
  const hashMap = {};
  const duplicates = [];

  files.forEach((file) => {
    const hash = getFileHash(file);
    if (hashMap[hash]) {
      duplicates.push({ original: hashMap[hash], duplicate: file });
    } else {
      hashMap[hash] = file;
    }
  });

  return duplicates;
}

function findUnusedFiles(files) {
  const usageMap = new Map();
  files.forEach((file) => usageMap.set(file, false));

  files.forEach((file) => {
    const content = fs.readFileSync(file, "utf-8");
    files.forEach((target) => {
      if (
        file !== target &&
        content.includes(
          path
            .basename(target)
            .replace(/\.(js|jsx|ts|tsx|css|json|vue|html)$/, "")
        )
      ) {
        usageMap.set(target, true);
      }
    });
  });

  return [...usageMap.entries()]
    .filter(([_, used]) => !used)
    .map(([file]) => file);
}

module.exports = {
  getAllFiles,
  findDuplicates,
  findUnusedFiles,
};
