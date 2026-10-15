<?php

namespace App\Tenancy;

/**
 * O que a pessoa que abriu o link do convite precisa fazer para aceitá-lo.
 */
enum InvitationAcceptanceMode: string
{
    /** Ninguém logado e não há conta com o e-mail convidado. */
    case CreateAccount = 'create_account';

    /** Ninguém logado, mas já existe conta com o e-mail convidado. */
    case Login = 'login';

    /** Logado com o e-mail convidado. */
    case Accept = 'accept';

    /** Logado com outro e-mail. */
    case WrongAccount = 'wrong_account';
}
