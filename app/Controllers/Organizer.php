<?php

namespace App\Controllers;

use App\Services\OrganizerService;

class Organizer extends BaseController
{
    protected OrganizerService $organizerService;

    public function __construct()
    {
        $this->organizerService = new OrganizerService();
    }

    /**
     * Página para se tornar organizador
     */
    public function become()
    {
        // Se já é organizador, redireciona
        if (auth()->user()->is_organizer) {
            return redirect()->to('organizer/dashboard');
        }

        return view('organizer/become');
    }

    /**
     * Processa o formulário de registro de organizador
     */
    public function register()
    {
        $rules = [
            'company_name'  => 'required|min_length[3]|max_length[255]',
            'document'      => 'required|min_length[11]|max_length[18]',
            'phone'         => 'required|min_length[10]|max_length[20]',
            'address'       => 'required|max_length[255]',
            'city'          => 'required|max_length[100]',
            'state'         => 'required|max_length[2]',
            'zip_code'      => 'required|max_length[10]',
            'business_type' => 'required|in_list[individual,company]',
            'terms'         => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->organizerService->processOrganizerRegistration(
            auth()->id(),
            $this->request->getPost()
        );

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        // Redireciona para o onboarding do Stripe
        if (!empty($result['onboarding_url'])) {
            return redirect()->to($result['onboarding_url']);
        }

        return redirect()->to('organizer/account-status');
    }

    /**
     * Página de status da conta
     */
    public function accountStatus()
    {
        $user = auth()->user();

        if (!$user->is_organizer) {
            return redirect()->to('organizer/become');
        }

        // Atualiza o status
        $this->organizerService->updateOrganizerStatus(auth()->id());
        
        // Recarrega o usuário
        $user = auth()->getProvider()->findById(auth()->id());

        $data = [
            'user'   => $user,
            'status' => $user->stripe_account_status,
        ];

        return view('organizer/account_status', $data);
    }

    /**
     * Callback após completar o onboarding
     */
    public function onboardingComplete()
    {
        // Atualiza o status da conta
        $result = $this->organizerService->updateOrganizerStatus(auth()->id());

        if ($result['success'] && $result['status'] === 'active') {
            return redirect()->to('organizer/dashboard')->with('success', 'Parabéns! Sua conta de organizador foi ativada com sucesso!');
        }

        return redirect()->to('organizer/account-status')->with('info', 'Verificando o status da sua conta...');
    }

    /**
     * Refresh do onboarding
     */
    public function onboardingRefresh()
    {
        $user = auth()->user();

        if (!$user->stripe_account_id) {
            return redirect()->to('organizer/become');
        }

        // Cria novo link de onboarding
        $result = $this->organizerService->createAccountLink(
            $user->stripe_account_id,
            base_url('organizer/onboarding-complete'),
            base_url('organizer/onboarding-refresh')
        );

        if ($result['success']) {
            return redirect()->to($result['url']);
        }

        return redirect()->to('organizer/account-status')->with('error', 'Erro ao gerar link de cadastro.');
    }

    /**
     * Dashboard do organizador
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        $eventModel = model('EventModel');
        $orderModel = model('OrderModel');

        // Estatísticas
        $stats = $orderModel->getOrganizerStats(auth()->id());
        $eventCounts = $eventModel->countByStatus(auth()->id());

        // Saldo Stripe
        $balance = ['available' => 0, 'pending' => 0];
        if ($user->stripe_account_id) {
            $balanceResult = $this->organizerService->getAccountBalance($user->stripe_account_id);
            if ($balanceResult['success']) {
                $balance = $balanceResult;
            }
        }

        // Eventos recentes
        $recentEvents = $eventModel->where('user_id', auth()->id())
                                   ->orderBy('created_at', 'DESC')
                                   ->findAll(5);

        $data = [
            'user'         => $user,
            'stats'        => $stats,
            'eventCounts'  => $eventCounts,
            'balance'      => $balance,
            'recentEvents' => $recentEvents,
        ];

        return view('organizer/dashboard', $data);
    }

    /**
     * Acessa o dashboard do Stripe
     */
    public function stripeDashboard()
    {
        $user = auth()->user();

        if (!$user->stripe_account_id) {
            return redirect()->to('organizer/account-status');
        }

        $result = $this->organizerService->createLoginLink($user->stripe_account_id);

        if ($result['success']) {
            return redirect()->to($result['url']);
        }

        return redirect()->back()->with('error', 'Erro ao acessar o painel financeiro.');
    }
}
