// Conteúdo estático da página Welcome.vue

export const navLinks = [
    { label: 'Início', href: '#inicio' },
    { label: 'Como funciona', href: '#como-funciona' },
    { label: 'Recursos', href: '#recursos' },
    { label: 'Benefícios', href: '#beneficios' },
];

// Ícones que flutuam ao redor do notebook no hero
export const floatingBadges = [
    { icon: 'mdi:white-balance-sunny', class: 'left-4 top-2 bg-amber-100 text-amber-500', delay: '0s' },
    { icon: 'mdi:chart-line', class: 'right-6 top-0 bg-brand-100 text-brand-700', delay: '1s' },
    { icon: 'mdi:solar-panel', class: 'right-0 top-1/2 bg-sky-100 text-sky-600', delay: '2s' },
    { icon: 'mdi:check-decagram', class: '-left-2 bottom-12 bg-emerald-100 text-emerald-600', delay: '1.5s' },
];

export const steps = [
    { icon: 'mdi:account-plus-outline', title: 'Cadastre', text: 'Registre clientes, endereços e os sistemas fotovoltaicos instalados.' },
    { icon: 'mdi:clipboard-check-outline', title: 'Vistorie', text: 'Preencha formulários de vistoria em campo, com fotos e anexos.' },
    { icon: 'mdi:file-certificate-outline', title: 'Homologue', text: 'Acompanhe cada processo junto à concessionária até a aprovação.' },
];

export const features = [
    { icon: 'mdi:clipboard-text-outline', color: 'bg-brand-50 text-brand-700', title: 'Formulários de vistoria', text: 'Monte questionários personalizados e padronize as vistorias da equipe.' },
    { icon: 'mdi:account-group-outline', color: 'bg-amber-50 text-amber-500', title: 'Gestão de clientes', text: 'Cadastro completo de clientes, contatos, endereços e sistemas.' },
    { icon: 'mdi:solar-panel', color: 'bg-sky-50 text-sky-600', title: 'Módulos e inversores', text: 'Controle os equipamentos instalados em cada cliente.' },
    { icon: 'mdi:file-document-multiple-outline', color: 'bg-violet-50 text-violet-500', title: 'Processos', text: 'Acompanhe processos de homologação junto aos órgãos emissores.' },
    { icon: 'mdi:package-variant-closed', color: 'bg-rose-50 text-rose-500', title: 'Estoque', text: 'Entradas, saídas e histórico de materiais sempre atualizados.' },
    { icon: 'mdi:calendar-month-outline', color: 'bg-emerald-50 text-emerald-600', title: 'Agenda e atividades', text: 'Organize a equipe com calendário, kanban e atividades.' },
    { icon: 'mdi:map-marker-radius-outline', color: 'bg-orange-50 text-orange-500', title: 'Mapa de clientes', text: 'Visualize no mapa onde estão os sistemas e as vistorias.' },
];

export const benefits = [
    'Vistorias padronizadas e sem papelada',
    'Equipe conectada em tempo real',
    'Histórico completo de cada cliente',
    'Estoque e equipamentos integrados',
];
