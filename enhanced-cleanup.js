// project-root/enhanced-cleanup.js

const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");
const readline = require("readline");
const {
  getAllFiles,
  findDuplicates,
  findUnusedFiles,
} = require("./lib/cleanupUtils");

const projectRoot = path.resolve(__dirname);
const backupDir = path.join(projectRoot, "backup");
const logFile = path.join(projectRoot, `cleanup-log-${Date.now()}.txt`);
const isDryRun = process.argv.includes("--dry-run");

function log(message) {
  fs.appendFileSync(logFile, `${new Date().toISOString()} - ${message}\n`);
}

function backupFile(filePath) {
  const relativePath = path.relative(projectRoot, filePath);
  const destPath = path.join(backupDir, relativePath);
  fs.mkdirSync(path.dirname(destPath), { recursive: true });
  fs.copyFileSync(filePath, destPath);
  log(`Backed up: ${filePath} → ${destPath}`);
}

function checkGitStatus() {
  const status = execSync("git status --porcelain").toString().trim();
  if (status) {
    throw new Error(
      "⚠️ Git working directory is not clean. Commit or stash changes before cleanup."
    );
  }
}

function promptUser(question) {
  return new Promise((resolve) => {
    const rl = readline.createInterface({
      input: process.stdin,
      output: process.stdout,
    });
    rl.question(question, (answer) => {
      rl.close();
      resolve(answer.trim().toLowerCase());
    });
  });
}

async function cleanup() {
  try {
    checkGitStatus();
    fs.mkdirSync(backupDir, { recursive: true });
    log("Starting cleanup...");

    const allFiles = getAllFiles(projectRoot, [
      "js",
      "jsx",
      "ts",
      "tsx",
      "css",
      "json",
      "vue",
      "html",
    ]);
    const unusedFiles = findUnusedFiles(allFiles);
    const duplicateFiles = findDuplicates(allFiles);
    const filesToDelete = unusedFiles.concat(
      duplicateFiles.map((d) => d.duplicate)
    );

    log(`Found ${unusedFiles.length} unused files.`);
    log(`Found ${duplicateFiles.length} duplicate files.`);

    unusedFiles.forEach((file) => log(`Unused: ${file}`));
    duplicateFiles.forEach(({ original, duplicate }) =>
      log(`Duplicate: ${duplicate} (of ${original})`)
    );

    if (!isDryRun) {
      const answer = await promptUser(
        `⚠️ About to delete ${filesToDelete.length} files. Type 'yes' to confirm: `
      );
      if (answer !== "yes") {
        console.log("❌ Cleanup cancelled by user.");
        log("Cleanup cancelled by user.");
        return;
      }
    }

    let deletedCount = 0;
    filesToDelete.forEach((file) => {
      if (isDryRun) {
        log(`[Dry-Run] Would delete: ${file}`);
        console.log(`[Dry-Run] Would delete: ${file}`);
      } else {
        backupFile(file);
        fs.unlinkSync(file);
        log(`Deleted: ${file}`);
        console.log(`Deleted: ${file}`);
        deletedCount++;
      }
    });

    // Summary
    console.log("\n📊 Cleanup Summary");
    console.log(` - Unused files: ${unusedFiles.length}`);
    console.log(` - Duplicate files: ${duplicateFiles.length}`);
    console.log(` - Files deleted: ${isDryRun ? 0 : deletedCount}`);
    console.log(` - Log file: ${logFile}`);
    console.log(` - Backup folder: ${backupDir}`);

    log("Cleanup completed successfully.");
  } catch (err) {
    log(`Error: ${err.message}`);
    console.error(err.message);
  }
}

cleanup();
