import Database from 'better-sqlite3';
import path from 'path';
import { fileURLToPath } from 'url';
import bcrypt from 'bcryptjs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const dbPath = path.join(__dirname, '..', '..', 'data.db');

const db = new Database(dbPath);
db.pragma('journal_mode = WAL');

// Create tables
db.exec(`
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  passwordHash TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('recepcao','enfermagem','medico','admin')),
  createdAt TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS patients (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  idade INTEGER,
  genero TEXT,
  alergias TEXT,
  gestante INTEGER DEFAULT 0,
  createdAt TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS triages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  patientId INTEGER NOT NULL,
  userId INTEGER,
  sintomas TEXT,
  descricaoLivre TEXT,
  resultado JSON,
  createdAt TEXT NOT NULL,
  FOREIGN KEY(patientId) REFERENCES patients(id),
  FOREIGN KEY(userId) REFERENCES users(id)
);
`);

// Seed users if empty
const userCount = db.prepare('SELECT COUNT(*) as c FROM users').get().c;
if (userCount === 0) {
  const users = [
    { name: 'Admin', email: 'admin@ubs.local', role: 'admin' },
    { name: 'Recepção', email: 'recepcao@ubs.local', role: 'recepcao' },
    { name: 'Enfermagem', email: 'enfermagem@ubs.local', role: 'enfermagem' },
    { name: 'Médico', email: 'medico@ubs.local', role: 'medico' }
  ];
  const hash = bcrypt.hashSync('123456', 10);
  const stmt = db.prepare('INSERT INTO users (name, email, passwordHash, role, createdAt) VALUES (?, ?, ?, ?, ?)');
  const now = new Date().toISOString();
  users.forEach(u => stmt.run(u.name, u.email, hash, u.role, now));
  console.log('Usuários padrão criados (senha: 123456).');
}

export default db;