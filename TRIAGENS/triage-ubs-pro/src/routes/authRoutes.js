import { Router } from 'express';
import db from '../db.js';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';

const router = Router();

router.post('/login', (req, res) => {
  const { email, password } = req.body || {};
  if (!email || !password) return res.status(400).json({ error: 'Credenciais inválidas' });
  const user = db.prepare('SELECT * FROM users WHERE email = ?').get(email);
  if (!user) return res.status(401).json({ error: 'Usuário não encontrado' });
  if (!bcrypt.compareSync(password, user.passwordHash)) return res.status(401).json({ error: 'Senha incorreta' });

  const token = jwt.sign({ id: user.id, name: user.name, role: user.role }, process.env.JWT_SECRET || 'devsecret', { expiresIn: '8h' });
  res.json({ token, user: { id: user.id, name: user.name, role: user.role, email: user.email } });
});

export default router;