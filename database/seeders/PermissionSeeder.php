<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa o cache de permissões - O passo mais importante!
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista completa de permissões baseada no seu menu
        $permissions = [
            // Cadastros
            'ver-produtos', 'criar-produtos', 'editar-produtos', 'excluir-produtos',
            'ver-fornecedores', 'criar-fornecedores', 'editar-fornecedores', 'excluir-fornecedores',
            'ver-clientes', 'criar-clientes', 'editar-clientes', 'excluir-clientes',
            'ver-categorias',
            'ver-usuarios', 'criar-usuarios', 'editar-usuarios', 'excluir-usuarios',
            'ver-perfis', 'criar-perfis', 'editar-perfis', 'excluir-perfis',
            'ver-transportadoras',
            'ver-servicos',
            'ver-formas-pagamento',

            // Compras
            'ver-compras', 'criar-compras', 'importar-xml-compras', 'cancelar-compras',
            'ver-solicitacoes-compra',
            'ver-cotacoes',
            'ver-pedidos-compra',

            // Vendas
            'ver-vendas', 'criar-vendas', 'editar-vendas', 'cancelar-vendas',
            'ver-leads',
            'ver-orcamentos',
            'ver-pedidos-venda',
            'ver-notas-fiscais',
            'ver-comissoes',

            // Financeiro
            'ver-financeiro',
            'ver-contas-receber',
            'ver-contas-pagar',
            'ver-fluxo-caixa',
            'ver-conciliacao-bancaria',
            'ver-centro-custo',

            // Estoque
            'ver-estoque',
            'ver-movimentacoes-estoque',
            'ver-transferencias-estoque',
            'ver-inventario',
            'ver-posicao-estoque',

            // Produção
            'ver-ordem-producao',
            'ver-estrutura-produto',
            'ver-ordem-servico',

            // Relatórios
            'ver-relatorio-vendas',
            'ver-relatorio-financeiro',
            'ver-relatorio-estoque',
            'ver-relatorio-compras',

            // Configurações
            'ver-config-empresa',
            'ver-config-fiscal',
            'ver-logs-sistema',
            'acessar-admin',
        ];

        // Cria as permissões que não existirem
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Perfis ---
        // Cria o perfil de Admin se ele não existir
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        // Atribui TODAS as permissões ao Administrador
        $adminRole->syncPermissions(Permission::all());

        // Cria o perfil Motoboy (exemplo mínimo)
        $motoboyRole = Role::firstOrCreate(['name' => 'usuario']);
        $motoboyRole->syncPermissions([
            'ver-pedidos-venda', // Exemplo: motoboy só pode ver os pedidos de venda
        ]);
    }
}