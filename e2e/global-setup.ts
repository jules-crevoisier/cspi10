import { execSync } from 'node:child_process';
import path from 'node:path';

export default function globalSetup(): void {
  const root = path.resolve(__dirname, '..');
  execSync('php scripts/e2e-setup.php', {
    cwd: root,
    stdio: 'inherit',
    env: {
      ...process.env,
      DATABASE_PATH: 'database/data/cspi-test.db',
      APP_ENV: 'test',
    },
  });
}
