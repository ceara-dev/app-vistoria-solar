// Textos do contrato de validação (.docs/VALIDATION.md). O backend usa os mesmos em lang/pt_BR/validation.php.
export const MESSAGES = {
    VALIDATION: {
        REQUIRED: 'Este campo é obrigatório',
        MAX_LENGTH: 'Máximo de {n} caracteres',
        MIN_LENGTH: 'Mínimo de {n} caracteres',
        INVALID_EMAIL: 'E-mail inválido',
        CONFIRMED: 'A confirmação não confere',
        UNIQUE: 'Este valor já está em uso',
        EXISTS: 'O valor selecionado é inválido',
        INVALID_CPF: 'CPF inválido',
        INVALID_CNPJ: 'CNPJ inválido',
        INVALID_PHONE: 'Telefone inválido',
        INVALID_ZIP_CODE: 'CEP inválido',
        INVALID_NUMBER: 'Valor inválido',
        MIN_VALUE: 'Valor mínimo: {n}',
    },
    COMMON: {
        CONFIRM_DEACTIVATE_MEMBER: 'Remover o acesso desta pessoa à empresa?',
        CONFIRM_REVOKE_INVITATION: 'Cancelar este convite?',
        CONFIRM_DELETE_CUSTOMER: 'Excluir este cliente? Os endereços e contatos dele também saem da lista.',
        CONFIRM_SUSPEND_TENANT: 'Suspender o acesso desta empresa?',
        CONFIRM_REACTIVATE_TENANT: 'Reativar o acesso desta empresa?',
        CONFIRM_EXTEND_TRIAL: 'Estender o teste desta empresa por 14 dias?',
        CONFIRM_DELETE_B2B_CLIENT: 'Excluir este cliente B2B? As empresas vinculadas ficam sem o vínculo.',
    },
} as const;
