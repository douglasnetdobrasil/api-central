<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NFCeService;
use Illuminate\Support\Facades\Log;

class NfeEnviarContingencia extends Command
{
    /**
     * A assinatura do comando no terminal.
     * @var string
     */
    protected $signature = 'nfe:enviar-contingencia';

    /**
     * A descrição do comando.
     * @var string
     */
    protected $description = 'Verifica e envia as NFC-es que foram emitidas em modo de contingência e estão pendentes.';

    /**
     * Executa a lógica do comando.
     *
     * @return int
     */
    public function handle(NFCeService $nfceService)
    {
        $this->info('Iniciando o processo de envio de NFC-es em contingência...');
        Log::info('Tarefa agendada: Iniciando envio de NFC-es em contingência.');

        // Chama a função que criamos no nosso serviço
        $resultado = $nfceService->enviarNotasEmContingencia();

        $this->info($resultado['message']);
        Log::info('Tarefa agendada: ' . $resultado['message']);
        
        $this->info('Processo finalizado.');
        return 0;
    }
}