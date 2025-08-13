// Integração opcional com provedor de IA (OpenAI via API de texto).
// Define OPENAI_API_KEY no .env para habilitar. Caso contrário, usa só o motor local.
export async function askLLMFreeText({ apiKey, prompt }) {
  if (!apiKey) return null;
  try {
    const resp = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        model: 'gpt-4o-mini',
        messages: [
          { role: 'system', content: 'Você é um assistente médico para triagem educacional. NUNCA dê diagnóstico definitivo. Sempre recomende avaliação profissional quando apropriado.' },
          { role: 'user', content: prompt }
        ],
        temperature: 0.2
      })
    });
    if (!resp.ok) return null;
    const data = await resp.json();
    return data.choices?.[0]?.message?.content || null;
  } catch (e) {
    return null;
  }
}