// project-root/scripts/cli/utils/backup-utils.ts

export type BackupEntry = {
  id: string;
  timestamp: string; // ISO format
  size: number; // in bytes
  files: string[]; // list of file paths
};

export function summarizeBackups(backups: BackupEntry[]): void {
  if (backups.length === 0) {
    console.log('No backups to summarize.');
    return;
  }

  console.log(`\n📦 Backup Summary (${backups.length} total):\n`);

  backups.forEach((backup, index) => {
    const ageDays = Math.floor((Date.now() - new Date(backup.timestamp).getTime()) / (1000 * 60 * 60 * 24));
    const sizeMB = (backup.size / (1024 * 1024)).toFixed(2);
    const fileCount = backup.files.length;

    console.log(`🔹 Backup #${index + 1}`);
    console.log(`   ID: ${backup.id}`);
    console.log(`   Age: ${ageDays} days`);
    console.log(`   Size: ${sizeMB} MB`);
    console.log(`   Files: ${fileCount}`);
    console.log('');
  });
}
