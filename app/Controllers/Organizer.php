<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\OrganizerService;

class Organizer extends BaseController
{
    protected $organizerService;

    public function __construct()
    {
        $this->organizerService = new OrganizerService();
    }

    /**
     * Dashboard do organizador
     */
    public function index()
    {
        $user = auth()->user();
        $eventModel = model('EventModel');
        $orderModel = model('OrderModel');

        // Estatísticas
        $events = $eventModel->findByOrganizer($user->id);
        $eventIds = array_column($events, 'id');
        
        $totalSales = 0;
        $totalOrders = 0;
        
        if (!empty($eventIds)) {
            foreach ($eventIds as $eventId) {
                $totalSales += $orderModel->getTotalSales($eventId);
            }
            $totalOrders = $orderModel->whereIn('event_id', $eventIds)
                                       ->where('status', 'paid')
                                       ->countAllResults();
        }

        $data = [
            'events'       => $events,
            'eventCounts'  => $eventModel->countByStatus($user->id),
            'totalSales'   => $totalSales,
            'totalOrders'  => $totalOrders,
            'recentOrders' => !empty($eventIds) 
                ? $orderModel->whereIn('event_id', $eventIds)->orderBy('created_at', 'DESC')->limit(5)->findAll() 
                : [],
        ];

        return view('organizer/dashboard', $data);
    }

    /**
     * Formulário de cadastro como organizador
     */
    public function register()
    {
        $user = auth()->user();

        // Se já é organizador, redireciona
        if ($user->is_organizer) {
            return redirect()->to('organizer');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'company_name'     => 'required|min_length[3]|max_length[255]',
                'company_document' => 'required|min_length[11]|max_length[20]',
                'phone'            => 'required|min_length[10]|max_length[20]',
                'address'          => 'required|min_length[10]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Atualiza o usuário
            $userProvider = auth()->getProvider();
            $user->fill([
                'is_organizer'     => 1,
                'company_name'     => $this->request->getPost('company_name'),
                'company_document' => preg_replace('/[^0-9]/', '', $this->request->getPost('company_document')),
                'phone'            => preg_replace('/[^0-9]/', '', $this->request->getPost('phone')),
                'address'          => $this->request->getPost('address'),
            ]);

            $userProvider->save($user);

            return redirect()->to('organizer/stripe/connect')->with('success', 'Cadastro realizado! Agora conecte sua conta Stripe.');
        }

        return view('organizer/register');
    }

    /**
     * Página para conectar conta Stripe
     */
    public function stripeConnect()
    {
        $user = auth()->user();

        if (!$user->is_organizer) {
            return redirect()->to('organizer/register');
        }

        if ($user->stripe_account_id && $user->stripe_onboarding_complete) {
            return redirect()->to('organizer');
        }

        return view('organizer/stripe_connect');
    }

    /**
     * Cria a conta Stripe Connect
     */
    public function stripeCreateAccount()
    {
        $user = auth()->user();

        if (!$user->is_organizer) {
            return redirect()->to('organizer/register');
        }

        try {
            // Cria ou recupera a conta Stripe
            if (empty($user->stripe_account_id)) {
                $account = $this->organizerService->createStripeAccount($user);
                
                // Salva o ID da conta
                $userProvider = auth()->getProvider();
                $user->stripe_account_id = $account->id;
                $userProvider->save($user);
            }

            // Gera o link de onboarding
            $onboardingUrl = $this->organizerService->createOnboardingLink(
                $user->stripe_account_id,
                base_url('organizer/stripe/return'),
                base_url('organizer/stripe/refresh')
            );

            return redirect()->to($onboardingUrl);

        } catch (\Exception $e) {
            log_message('error', 'Stripe Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao conectar com Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Retorno do onboarding Stripe
     */
    public function stripeReturn()
    {
        $user = auth()->user();

        if (empty($user->stripe_account_id)) {
            return redirect()->to('organizer/stripe/connect');
        }

        try {
            // Verifica o status da conta
            $accountStatus = $this->organizerService->checkAccountStatus($user->stripe_account_id);
            
            $userProvider = auth()->getProvider();
            $user->stripe_account_status = $accountStatus['status'];
            $user->stripe_onboarding_complete = $accountStatus['onboarding_complete'] ? 1 : 0;
            $userProvider->save($user);

            if ($accountStatus['onboarding_complete']) {
                return redirect()->to('organizer')->with('success', 'Conta Stripe configurada com sucesso!');
            } else {
                return redirect()->to('organizer/stripe/onboarding')->with('info', 'Complete a configuração da sua conta Stripe.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Stripe Return Error: ' . $e->getMessage());
            return redirect()->to('organizer/stripe/connect')->with('error', 'Erro ao verificar conta Stripe.');
        }
    }

    /**
     * Refresh do onboarding Stripe (se expirar)
     */
    public function stripeRefresh()
    {
        return redirect()->to('organizer/stripe/connect')->with('info', 'O link expirou. Clique novamente para continuar.');
    }

    /**
     * Página de onboarding pendente
     */
    public function stripeOnboarding()
    {
        $user = auth()->user();

        if (empty($user->stripe_account_id)) {
            return redirect()->to('organizer/stripe/connect');
        }

        if ($user->stripe_onboarding_complete) {
            return redirect()->to('organizer');
        }

        return view('organizer/stripe_onboarding');
    }

    /**
     * Verifica status da conta Stripe (AJAX)
     */
    public function checkStripeStatus()
    {
        $user = auth()->user();

        if (empty($user->stripe_account_id)) {
            return $this->response->setJSON(['status' => 'not_connected']);
        }

        try {
            $status = $this->organizerService->checkAccountStatus($user->stripe_account_id);
            return $this->response->setJSON($status);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }
}
