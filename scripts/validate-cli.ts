// project-root/scripts/validate-cli.ts

import fs from 'fs';
import path from 'path';
import ts from 'typescript';

const projectRoot = path.resolve(__dirname, '..');
const cliDir = path.join(projectRoot, 'src', 'cli');

// CLI args
const desiredFileName = process.argv.find(arg => arg.endsWith('.ts') || arg.endsWith('.json'));
const dryRun = process.argv.includes('--dry-run');
const summaryOnly = process.argv.includes('--summary-only');

function getAllFiles(dir: string, ext = '.ts'): string[] {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  return entries.flatMap(entry => {
    const res = path.resolve(dir, entry.name);
    return entry.isDirectory() ? getAllFiles(res, ext) : res.endsWith(ext) ? [res] : [];
  });
}

function checkExports(filePath: string): string[] {
  const source = ts.createSourceFile(filePath, fs.readFileSync(filePath, 'utf8'), ts.ScriptTarget.Latest);
  const missingExports: string[] = [];

  const hasExport = source.statements.some(stmt => {
    if (
      ts.isFunctionDeclaration(stmt) ||
      ts.isVariableStatement(stmt) ||
      ts.isClassDeclaration(stmt) ||
      ts.isInterfaceDeclaration(stmt) ||
      ts.isEnumDeclaration(stmt) ||
      ts.isTypeAliasDeclaration(stmt)
    ) {
      return stmt.modifiers?.some((m: ts.Modifier) => m.kind === ts.SyntaxKind.ExportKeyword);
    }
    return ts.isExportAssignment(stmt) || ts.isExportDeclaration(stmt);
  });

  if (!hasExport) missingExports.push(filePath);
  return missingExports;
}

function validatePaths(filePath: string): string[] {
  const source = ts.createSourceFile(filePath, fs.readFileSync(filePath, 'utf8'), ts.ScriptTarget.Latest);
  const errors: string[] = [];

  source.forEachChild(node => {
    if (ts.isImportDeclaration(node)) {
      const importPath = (node.moduleSpecifier as ts.StringLiteral).text;
      const resolved = path.resolve(path.dirname(filePath), importPath + '.ts');
      if (!fs.existsSync(resolved)) errors.push(`Broken import in ${filePath}: ${importPath}`);
    }
  });

  return errors;
}

function findFileAnywhere(fileName: string): string[] {
  return getAllFiles(projectRoot).filter(f => path.basename(f) === fileName);
}

// Run validations
const cliFiles = getAllFiles(cliDir);
const missingExports = cliFiles.flatMap(checkExports);
const brokenPaths = cliFiles.flatMap(validatePaths);
const foundFiles = desiredFileName ? findFileAnywhere(desiredFileName) : [];

if (!summaryOnly) {
  console.log('🔍 Validation Details');
  if (!dryRun) {
    missingExports.forEach(f => console.log(`❌ Missing export: ${f}`));
    brokenPaths.forEach(e => console.log(`🔗 ${e}`));
    if (desiredFileName) foundFiles.forEach(f => console.log(`📂 Found "${desiredFileName}" at: ${f}`));
  } else {
    console.log('🧪 Dry-run mode: No changes made, just reporting.');
  }
}

console.log('\n📋 Validation Summary');
console.log(`Files scanned: ${cliFiles.length}`);
console.log(`Missing exports: ${missingExports.length}`);
console.log(`Broken paths: ${brokenPaths.length}`);
if (desiredFileName) console.log(`Matches for "${desiredFileName}": ${foundFiles.length}`);
