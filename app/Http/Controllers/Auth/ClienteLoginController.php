<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Garanta que você tem a view de login (portal_cliente.login) criada
use Illuminate\Validation\ValidationException;

class ClienteLoginController extends Controller
{
    /**
     * Mostra o formulário de login do cliente.
     */
    public function showLoginForm()
    {
        return view('portal_cliente.login');
    }

    /**
     * Processa a tentativa de login do cliente.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('cliente')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('portal.dashboard')); 
        }

        // Use ValidationException para enviar os erros da forma correta
        throw ValidationException::withMessages([
            'email' => __('auth.failed'), // Mensagem padrão do Laravel
        ]);
    }

    /**
     * Faz o logout do cliente.
     */
    public function logout(Request $request)
    {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('portal.login'));
    }
}