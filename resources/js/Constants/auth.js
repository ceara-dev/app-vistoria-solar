// Conteúdo estático das páginas de autenticação (GuestLayout e Pages/Auth)

// Destaques exibidos no painel lateral
export const highlights = [
    { icon: 'mdi:clipboard-check-outline', text: 'Vistorias padronizadas e sem papelada' },
    { icon: 'mdi:account-group-outline', text: 'Equipe conectada em tempo real' },
    { icon: 'mdi:file-certificate-outline', text: 'Homologação acompanhada de perto' },
];

// Ícones que flutuam ao redor do notebook no painel lateral
export const panelBadges = [
    { icon: 'mdi:white-balance-sunny', class: '-left-3 top-0 bg-amber-100 text-amber-500', delay: '0s' },
    { icon: 'mdi:chart-line', class: '-right-3 top-6 bg-white text-brand-700', delay: '1s' },
    { icon: 'mdi:check-decagram', class: 'left-6 -bottom-2 bg-emerald-100 text-emerald-600', delay: '2s' },
];

// Níveis do medidor de força da senha (Register e ResetPassword)
export const passwordStrengthLevels = [
    { label: 'Muito fraca', class: 'progress-error' },
    { label: 'Fraca', class: 'progress-error' },
    { label: 'Média', class: 'progress-warning' },
    { label: 'Boa', class: 'progress-success' },
    { label: 'Forte', class: 'progress-success' },
];
