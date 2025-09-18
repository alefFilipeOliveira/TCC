
Triagem UPA — Versão v7 (Removida tabela; relatório complexo; UI melhorada)

Mudanças principais:
- A tabela/checklist de checkboxes foi removida. Agora há um campo pesquisável + "chips" (tags) para adicionar condições (entrada estruturada, sem tabela).
- O relatório gerado é maior e mais complexo, com sumário clínico, exame físico sugerido, dados pendentes e diagnóstico inferido.
- Classificações (VERDE / AMARELO / VERMELHO) têm comentários distintos e visual com efeitos animados.
- Botões possuem efeitos de carregamento (spinner) e animações pequenas para interação.
- Suporte a altura com vírgula ou ponto; aceita múltiplas condições (>=120 disponíveis na lista de sugestões).
- Para ativar IA: edite config.php e coloque sua OPENAI_API_KEY.
- Teste local com XAMPP: extraia a pasta em htdocs e acesse http://localhost/triagem_upa_v7/index.php
Login: EnfermeiroUpa2 / Upa2

Avisos: dados de pacientes enviados à OpenAI (quando ativada) devem ter consentimento e seguir normas de privacidade local.
