import { Router } from 'express';
import db from '../db.js';
import { authRequired } from '../middlewares/auth.js';

const router = Router();

router.get('/', authRequired, (req, res) => {
  const q = (req.query.q || '').trim();
  if (q) {
    const rows = db.prepare('SELECT * FROM patients WHERE name LIKE ? ORDER BY id DESC LIMIT 50').all(`%${q}%`);
    return res.json({ patients: rows });
  }
  const rows = db.prepare('SELECT * FROM patients ORDER BY id DESC LIMIT 50').all();
  res.json({ patients: rows });
});

router.get('/:id', authRequired, (req, res) => {
  const id = Number(req.params.id);
  const p = db.prepare('SELECT * FROM patients WHERE id = ?').get(id);
  if (!p) return res.status(404).json({ error: 'Paciente não encontrado' });
  res.json({ patient: p });
});

export default router;