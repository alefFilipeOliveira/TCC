/**
 * Mecanismo simples de "IA" baseado em regras + escore.
 * Objetivo: protótipo educacional de triagem para UBS.
 * NÃO é diagnóstico médico. Use com cautela.
 */

function normalizeText(t) {
    return (t || '').toString().toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');
}

function parseAge(ageStr) {
    const n = Number(ageStr);
    if (Number.isFinite(n) && n >= 0 && n < 130) return n;
    return null;
}

// Palavras-chave e pesos para escore de gravidade (quanto maior, mais grave)
const keywords = [
    { k: ['dor no peito', 'dor toracica', 'aperto no peito', 'dor forte no peito'], score: 6, tag: 'dor_peito' },
    { k: ['falta de ar', 'dificuldade para respirar', 'dispneia'], score: 6, tag: 'falta_ar' },
    { k: ['desmaio', 'sincopa', 'desmaiei'], score: 6, tag: 'desmaio' },
    { k: ['confusao', 'desorientacao'], score: 5, tag: 'confusao' },
    { k: ['sangramento intenso', 'hemorragia', 'sangrando muito'], score: 6, tag: 'sangramento' },
    { k: ['rigidez de pescoco', 'nuca rigida'], score: 5, tag: 'rigidez_pescoco' },
    { k: ['febre alta', 'febre 39', 'febre 40', 'muito febril'], score: 4, tag: 'febre_alta' },
    { k: ['febre', 'calafrios'], score: 2, tag: 'febre' },
    { k: ['tosse', 'tosse seca', 'tosse com catarro'], score: 1, tag: 'tosse' },
    { k: ['chiado', 'sibilo'], score: 2, tag: 'chiado' },
    { k: ['manchas vermelhas', 'exantema', 'erupcao'], score: 2, tag: 'exantema' },
    { k: ['vomito persistente', 'vomitando sem parar'], score: 4, tag: 'vomito_persistente' },
    { k: ['diarreia com sangue', 'diarréia com sangue'], score: 5, tag: 'diarreia_sangue' },
    { k: ['dor intensa', 'dor muito forte'], score: 4, tag: 'dor_intensa' },
    { k: ['dor de cabeca forte', 'cefaleia intensa', 'enxaqueca forte'], score: 3, tag: 'cefaleia_forte' },
    { k: ['fraqueza em um lado', 'boca torta', 'dificuldade de falar'], score: 7, tag: 'sinais_avc' },
    { k: ['gravida sangramento', 'sangramento na gravidez', 'gestante sangramento'], score: 7, tag: 'gestante_sangramento' },
    { k: ['desidratacao', 'sem urinar', 'urina escura', 'muito seco'], score: 4, tag: 'desidratacao' },
    { k: ['dor ao urinar', 'ardor ao urinar', 'urina com sangue'], score: 2, tag: 'urinario' },
    { k: ['dor abdominal', 'colica'], score: 2, tag: 'dor_abdominal' },
    { k: ['coceira', 'prurido'], score: 1, tag: 'prurido' },
    { k: ['coriza', 'nariz entupido'], score: 1, tag: 'resfriado' },
];

function classifyRisk(score, flags, age, isPregnant) {
    // Regras duras (gatilhos de emergência)
    const hard = ['sinais_avc', 'dor_peito', 'falta_ar', 'desmaio', 'sangramento', 'gestante_sangramento'];
    if (flags.some(f => hard.includes(f))) return { level: 'Vermelho', recommendation: 'Procure atendimento imediato (emergência).' };

    // Considera idade e febre alta como urgência
    if ((age !== null && (age < 5 || age >= 65)) && (flags.includes('febre_alta') || score >= 6)) {
        return { level: 'Amarelo', recommendation: 'Atendimento em caráter urgente.' };
    }

    if (score >= 7) return { level: 'Amarelo', recommendation: 'Atendimento em caráter urgente.' };
    if (score >= 3) return { level: 'Verde', recommendation: 'Pode aguardar atendimento não urgente / ambulatorial.' };
    return { level: 'Azul', recommendation: 'Orientação e serviços administrativos / sintomas leves.' };
}

function possibleConditions(flags, age) {
    const suggestions = [];
    if (flags.includes('tosse') && flags.includes('febre')) suggestions.push('Infecção respiratória (ex.: gripe/resfriado)');
    if (flags.includes('tosse') && flags.includes('chiado')) suggestions.push('Exacerbação asmática ou bronquite');
    if (flags.includes('dor_peito') && flags.includes('falta_ar')) suggestions.push('Causa cardiovascular ou pulmonar');
    if (flags.includes('cefaleia_forte') && flags.includes('rigidez_pescoco') && flags.includes('febre')) suggestions.push('Infecção do SNC (avaliar com urgência)');
    if (flags.includes('dor_abdominal') && flags.includes('vomito_persistente')) suggestions.push('Gastroenterite ou outra causa abdominal');
    if (flags.includes('exantema') && flags.includes('febre')) suggestions.push('Infecção exantemática');
    if (flags.includes('urinario')) suggestions.push('Infecção do trato urinário');
    if (flags.includes('desidratacao')) suggestions.push('Desidratação');
    if (flags.length === 0) suggestions.push('Sem sinais de gravidade aparentes pelos dados fornecidos');
    return suggestions;
}

function triage(input) {
    const {
        nome = '',
            idade = '',
            genero = '',
            alergias = '',
            sintomas = [],
            descricaoLivre = '',
            gestante = false
    } = input || {};

    const age = parseAge(idade);
    const desc = normalizeText(descricaoLivre);
    const symptomList = Array.isArray(sintomas) ? sintomas.map(s => normalizeText(s)) : [];

    // somar escore por sintomas marcados e por descrição livre
    let score = 0;
    const flags = new Set();

    const textForScan = [desc, ...symptomList].join(' | ');

    keywords.forEach(({ k, score: s, tag }) => {
        for (const kw of k) {
            if (textForScan.includes(kw)) {
                score += s;
                flags.add(tag);
                break;
            }
        }
    });

    // Ajustes de escore por idade/gravidez
    if (age !== null && (age < 5 || age >= 65)) score += 1;
    if (gestante) score += 1;

    const risk = classifyRisk(score, Array.from(flags), age, gestante);
    const conditions = possibleConditions(Array.from(flags), age);

    const predicted = conditions[0] || 'Sem hipótese aparente';
    const now = new Date().toISOString();

    // Monta um "relatório" simples
    const relatorio = {
        cabecalho: {
            geradoEm: now,
            sistema: 'Triagem Inteligente UPA (Protótipo)',
            avisoLegal: 'Ferramenta educacional. Não substitui avaliação profissional.'
        },
        paciente: {
            nome,
            idade: age,
            genero,
            alergias,
            gestante
        },
        entrada: {
            sintomas: symptomList,
            descricaoLivre
        },
        analiseIA: {
            escoreGravidade: score,
            marcadores: Array.from(flags),
            possiveisCausas: conditions
        },
        resultado: {
            classificacaoRisco: risk.level,
            recomendacao: risk.recommendation,
            hipotesePrincipal: predicted
        }
    };

    return relatorio;
}

module.exports = { triage };