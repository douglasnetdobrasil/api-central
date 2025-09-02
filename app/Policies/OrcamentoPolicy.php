<?php

namespace App\Policies;

use App\Models\Orcamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrcamentoPolicy
{
    /**
     * Regra geral para administradores: eles podem fazer tudo.
     * O 'before' é executado antes de qualquer outra regra.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        return null; // Se não for Admin, continua para as outras regras.
    }

    /**
     * Determina se o utilizador pode ver a lista de orçamentos.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver-orcamentos');
    }

    /**
     * Determina se o utilizador pode ver um orçamento específico.
     * REGRA: Precisa da permissão E ser da mesma empresa do orçamento.
     */
    public function view(User $user, Orcamento $orcamento): bool
    {
        return $user->can('ver-orcamentos') && $user->empresa_id === $orcamento->empresa_id;
    }

    /**
     * Determina se o utilizador pode criar orçamentos.
     */
    public function create(User $user): bool
    {
        return $user->can('criar-orcamentos');
    }

    /**
     * Determina se o utilizador pode atualizar um orçamento.
     * REGRA: Precisa da permissão E ser da mesma empresa do orçamento.
     */
    public function update(User $user, Orcamento $orcamento): bool
    {
        return $user->can('editar-orcamentos') && $user->empresa_id === $orcamento->empresa_id;
    }

    /**
     * Determina se o utilizador pode apagar um orçamento.
     * REGRA: Precisa da permissão E ser da mesma empresa do orçamento.
     */
    public function delete(User $user, Orcamento $orcamento): bool
    {
        return $user->can('excluir-orcamentos') && $user->empresa_id === $orcamento->empresa_id;
    }
}