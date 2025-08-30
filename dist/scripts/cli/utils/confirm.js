// project-root/scripts/cli/utils/confirm.ts
import readline from 'readline';
export function confirm(message, defaultNo = true) {
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    const suffix = defaultNo ? '(y/N)' : '(Y/n)';
    return new Promise(resolve => {
        rl.question(`${message} ${suffix}: `, answer => {
            rl.close();
            const normalized = answer.trim().toLowerCase();
            if (defaultNo)
                resolve(normalized === 'y');
            else
                resolve(normalized !== 'n');
        });
    });
}
