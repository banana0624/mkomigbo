// project-root/scripts/audit-imports.ts

/* Scans for relative imports missing .js, emits an audit report and onboarding flags */
import fs from 'node:fs';
import path from 'node:path';

type Violation = {
  file: string;
  line: number;
  kind: 'static' | 'dynamic';
  specifier: string;
};

type Report = {
  timestamp: string;
  totalFiles: number;
  totalRelativeImports: number;
  violations: Violation[];
  summary: {
    filesWithViolations: number;
    violationCount: number;
    compliancePercent: number; // 0-100
  };
};

const SRC_DIR = path.resolve(process.cwd(), 'src');
const OUT_DIR = path.resolve(process.cwd(), '.copilot');
const AUDIT_DIR = path.join(OUT_DIR, 'audit');
const ONBOARD_DIR = path.join(OUT_DIR, 'onboarding');
const AUDIT_FILE = path.join(AUDIT_DIR, 'report.json');
const FLAGS_FILE = path.join(ONBOARD_DIR, 'flags.json');

const relImportRe = /import\s+(?:type\s+)?(?:[^'"]*?\sfrom\s+)?['"](\.\.?\/[^'"]+)['"]/g;
const dynImportRe = /import\(\s*['"](\.\.?\/[^'"]+)['"]\s*\)/g;

function listFiles(dir: string, exts = ['.ts', '.tsx']): string[] {
  const out: string[] = [];
  const stack = [dir];
  while (stack.length) {
    const d = stack.pop()!;
    for (const ent of fs.readdirSync(d, { withFileTypes: true })) {
      const p = path.join(d, ent.name);
      if (ent.isDirectory()) {
        if (ent.name === 'node_modules' || ent.name.startsWith('.')) continue;
        stack.push(p);
      } else if (exts.includes(path.extname(ent.name))) {
        out.push(p);
      }
    }
  }
  return out;
}

function hasJsExt(spec: string): boolean {
  // Allow .js or .mjs only. Force explicit extension for TS under node16/nodenext.
  return spec.endsWith('.js') || spec.endsWith('.mjs');
}

function scan(): Report {
  const files = fs.existsSync(SRC_DIR) ? listFiles(SRC_DIR) : [];
  let totalRel = 0;
  const violations: Violation[] = [];

  for (const file of files) {
    const src = fs.readFileSync(file, 'utf8');
    // static imports
    for (const match of src.matchAll(relImportRe)) {
      totalRel++;
      const spec = match[1];
      if (!hasJsExt(spec)) {
        const start = match.index ?? 0;
        const line = src.slice(0, start).split('\n').length;
        violations.push({ file: path.relative(process.cwd(), file), line, kind: 'static', specifier: spec });
      }
    }
    // dynamic imports
    for (const match of src.matchAll(dynImportRe)) {
      totalRel++;
      const spec = match[1];
      if (!hasJsExt(spec)) {
        const start = match.index ?? 0;
        const line = src.slice(0, start).split('\n').length;
        violations.push({ file: path.relative(process.cwd(), file), line, kind: 'dynamic', specifier: spec });
      }
    }
  }

  const filesWithViolations = new Set(violations.map(v => v.file)).size;
  const violationCount = violations.length;
  const compliant = Math.max(totalRel - violationCount, 0);
  const compliancePercent = totalRel === 0 ? 100 : Math.round((compliant / totalRel) * 100);

  return {
    timestamp: new Date().toISOString(),
    totalFiles: files.length,
    totalRelativeImports: totalRel,
    violations,
    summary: { filesWithViolations, violationCount, compliancePercent },
  };
}

function ensureDir(p: string) {
  if (!fs.existsSync(p)) fs.mkdirSync(p, { recursive: true });
}

function main() {
  const report = scan();

  ensureDir(AUDIT_DIR);
  ensureDir(ONBOARD_DIR);

  fs.writeFileSync(AUDIT_FILE, JSON.stringify(report, null, 2), 'utf8');

  // Onboarding overlay flags to celebrate or nudge
  const flags = {
    showOnboardingOverlay: report.summary.violationCount > 0,
    badges: {
      jsExtensionCompliance: report.summary.compliancePercent,
    },
    tips: report.summary.violationCount > 0 ? [
      'Add .js to all relative imports when using node16/nodenext.',
      "Example: import { X } from './module.js';",
    ] : [
      'Great job! 100% .js extension compliance.',
    ],
  };
  fs.writeFileSync(FLAGS_FILE, JSON.stringify(flags, null, 2), 'utf8');

  // Console summary for CI
  const { violationCount, filesWithViolations, compliancePercent } = report.summary;
  if (violationCount > 0) {
    console.error(`Audit failed: ${violationCount} missing .js extensions across ${filesWithViolations} files (compliance ${compliancePercent}%).`);
    console.error(`Detailed report: ${path.relative(process.cwd(), AUDIT_FILE)}`);
    process.exitCode = 1;
  } else {
    console.log(`Audit passed: ${compliancePercent}% compliance. Report: ${path.relative(process.cwd(), AUDIT_FILE)}`);
  }
}

main();

