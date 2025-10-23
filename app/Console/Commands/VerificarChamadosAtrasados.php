<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuporteChamado; // <-- Importe o Model
use Illuminate\Support\Facades\Log; // <-- Importe o Log
use Carbon\Carbon; // <-- Importe o Carbon para manipulação de datas

class VerificarChamadosAtrasados extends Command
{
    /**
     * The name and signature of the console command.
     * Use um nome descritivo para chamar no terminal (ex: php artisan chamados:verificar-atrasos)
     */
    protected $signature = 'chamados:verificar-atrasos';

    /**
     * The console command description.
     */
    protected $description = 'Verifica chamados abertos ou aguardando atendimento por muito tempo e registra um log.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de chamados atrasados...'); // Mensagem no terminal

        // === Critério 1: Chamados ABERTOS (não atribuídos) há mais de X horas ===
        $limiteHorasAbertos = 4; // Defina seu SLA (Ex: 4 horas)
        $chamadosAbertosAtrasados = SuporteChamado::where('status', 'Aberto')
            ->whereNull('tecnico_atribuido_id') // Garante que são os não atribuídos
            ->where('created_at', '<', Carbon::now()->subHours($limiteHorasAbertos))
            ->get();

        if ($chamadosAbertosAtrasados->isNotEmpty()) {
            $this->warn("Encontrados {$chamadosAbertosAtrasados->count()} chamados abertos há mais de {$limiteHorasAbertos}h:");
            foreach ($chamadosAbertosAtrasados as $chamado) {
                // Loga um aviso para cada chamado encontrado
                Log::warning("Alerta Atraso: Chamado Aberto #{$chamado->protocolo} (Cliente: {$chamado->cliente_id}) está aberto há mais de {$limiteHorasAbertos}h sem atribuição.");
                $this->line("- Protocolo: {$chamado->protocolo} (Aberto em: {$chamado->created_at->format('d/m/Y H:i')})"); // Mostra no terminal
            }
        } else {
             $this->info("Nenhum chamado 'Aberto' atrasado encontrado.");
        }

        // === Critério 2: Chamados AGUARDANDO ATENDIMENTO (cliente respondeu) há mais de Y horas ===
        $limiteHorasAguardando = 24; // Defina seu SLA (Ex: 24 horas para responder o cliente)
        $chamadosAguardandoAtrasados = SuporteChamado::where('status', 'Aguardando Atendimento')
            ->whereNotNull('tecnico_atribuido_id') // Garante que já tem técnico
            ->where('updated_at', '<', Carbon::now()->subHours($limiteHorasAguardando)) // Usa updated_at como referência da última resposta do cliente (ou mudança de status)
            ->get();

         if ($chamadosAguardandoAtrasados->isNotEmpty()) {
            $this->warn("Encontrados {$chamadosAguardandoAtrasados->count()} chamados aguardando resposta do técnico há mais de {$limiteHorasAguardando}h:");
            foreach ($chamadosAguardandoAtrasados as $chamado) {
                // Loga um aviso
                Log::warning("Alerta Atraso: Chamado #{$chamado->protocolo} (Técnico: {$chamado->tecnico_atribuido_id}) está aguardando resposta há mais de {$limiteHorasAguardando}h.");
                 $this->line("- Protocolo: {$chamado->protocolo} (Última atualização: {$chamado->updated_at->format('d/m/Y H:i')})"); // Mostra no terminal
            }
        } else {
             $this->info("Nenhum chamado 'Aguardando Atendimento' atrasado encontrado.");
        }
        
        // Adicionar outros critérios se necessário (Ex: 'Em Atendimento' parado há muito tempo)

        $this->info('Verificação concluída.');
        return Command::SUCCESS; // Indica que o comando executou com sucesso
    }
}