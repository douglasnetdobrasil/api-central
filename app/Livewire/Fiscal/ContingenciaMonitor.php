<?php

namespace App\Livewire\Fiscal;

use Livewire\Component;
use App\Models\Nfe;
use Illuminate\Support\Facades\Auth;
use App\Services\NFCeService;

class ContingenciaMonitor extends Component
{
    public function sincronizarManualmente(NFCeService $nfceService)
    {
        try {
            $resultado = $nfceService->enviarNotasEmContingencia();
            session()->flash('message', $resultado['message']);
        } catch (\Exception $e) {
            session()->flash('error', 'Ocorreu um erro ao tentar sincronizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // ... (a lógica de busca das notas pendentes continua a mesma)
        $notasPendentes = Nfe::with('venda')
                            ->where('empresa_id', Auth::user()->empresa_id)
                            ->where('status', 'contingencia_pendente')
                            ->latest()
                            ->get();
    
        return view('livewire.fiscal.contingencia-monitor', [
            'notasPendentes' => $notasPendentes
        ]); // <-- REMOVA OU COMENTE A LINHA DO LAYOUT
    }
}