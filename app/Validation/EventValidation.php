<?php

namespace App\Validation;

class EventValidation
{
    /**
     * Regras de validação para eventos
     */
    public function getRules(): array
    {
        return [
            'title' => [
                'label' => 'Título',
                'rules' => 'required|min_length[3]|max_length[255]',
            ],
            'description' => [
                'label' => 'Descrição',
                'rules' => 'permit_empty|max_length[5000]',
            ],
            'venue_name' => [
                'label' => 'Nome do local',
                'rules' => 'required|min_length[3]|max_length[255]',
            ],
            'venue_address' => [
                'label' => 'Endereço',
                'rules' => 'required|min_length[5]',
            ],
            'venue_city' => [
                'label' => 'Cidade',
                'rules' => 'required|min_length[2]|max_length[100]',
            ],
            'venue_state' => [
                'label' => 'Estado',
                'rules' => 'required|exact_length[2]|alpha',
            ],
            'venue_zipcode' => [
                'label' => 'CEP',
                'rules' => 'required|min_length[8]|max_length[10]',
            ],
            'category' => [
                'label' => 'Categoria',
                'rules' => 'required|in_list[show,theater,sports,festival,conference,workshop,party,exhibition,other]',
            ],
            'max_tickets_per_purchase' => [
                'label' => 'Máximo de ingressos por compra',
                'rules' => 'permit_empty|integer|greater_than[0]|less_than[100]',
            ],
            'image' => [
                'label' => 'Imagem do evento',
                'rules' => 'permit_empty|uploaded[image]|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
            ],
            'banner' => [
                'label' => 'Banner do evento',
                'rules' => 'permit_empty|uploaded[banner]|is_image[banner]|max_size[banner,5120]|mime_in[banner,image/jpg,image/jpeg,image/png,image/webp]',
            ],
        ];
    }

    /**
     * Mensagens de validação customizadas
     */
    public function getMessages(): array
    {
        return [
            'title' => [
                'required'   => 'O título do evento é obrigatório.',
                'min_length' => 'O título deve ter pelo menos {param} caracteres.',
                'max_length' => 'O título não pode ter mais de {param} caracteres.',
            ],
            'venue_name' => [
                'required'   => 'O nome do local é obrigatório.',
                'min_length' => 'O nome do local deve ter pelo menos {param} caracteres.',
            ],
            'venue_address' => [
                'required'   => 'O endereço é obrigatório.',
                'min_length' => 'O endereço deve ter pelo menos {param} caracteres.',
            ],
            'venue_city' => [
                'required' => 'A cidade é obrigatória.',
            ],
            'venue_state' => [
                'required'     => 'O estado é obrigatório.',
                'exact_length' => 'O estado deve ter exatamente 2 letras (sigla).',
                'alpha'        => 'O estado deve conter apenas letras.',
            ],
            'venue_zipcode' => [
                'required'   => 'O CEP é obrigatório.',
                'min_length' => 'O CEP deve ter pelo menos {param} caracteres.',
            ],
            'category' => [
                'required' => 'A categoria é obrigatória.',
                'in_list'  => 'Selecione uma categoria válida.',
            ],
            'max_tickets_per_purchase' => [
                'integer'      => 'O número máximo de ingressos deve ser um número inteiro.',
                'greater_than' => 'O número máximo de ingressos deve ser maior que {param}.',
                'less_than'    => 'O número máximo de ingressos deve ser menor que {param}.',
            ],
            'image' => [
                'uploaded' => 'Selecione uma imagem válida.',
                'is_image' => 'O arquivo deve ser uma imagem.',
                'max_size' => 'A imagem não pode ter mais de 2MB.',
                'mime_in'  => 'A imagem deve ser JPG, PNG ou WebP.',
            ],
            'banner' => [
                'uploaded' => 'Selecione um banner válido.',
                'is_image' => 'O arquivo deve ser uma imagem.',
                'max_size' => 'O banner não pode ter mais de 5MB.',
                'mime_in'  => 'O banner deve ser JPG, PNG ou WebP.',
            ],
        ];
    }

    /**
     * Regras para validação de setores
     */
    public function getSectorRules(): array
    {
        return [
            'name' => [
                'label' => 'Nome do setor',
                'rules' => 'required|min_length[1]|max_length[100]',
            ],
            'price' => [
                'label' => 'Preço',
                'rules' => 'required|decimal|greater_than_equal_to[0]',
            ],
            'color' => [
                'label' => 'Cor',
                'rules' => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            ],
        ];
    }

    /**
     * Regras para validação de dias do evento
     */
    public function getEventDayRules(): array
    {
        return [
            'date' => [
                'label' => 'Data',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
            'start_time' => [
                'label' => 'Horário de início',
                'rules' => 'required|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            ],
            'end_time' => [
                'label' => 'Horário de término',
                'rules' => 'permit_empty|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            ],
            'doors_open_time' => [
                'label' => 'Abertura dos portões',
                'rules' => 'permit_empty|regex_match[/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            ],
        ];
    }

    /**
     * Mensagens para dias do evento
     */
    public function getEventDayMessages(): array
    {
        return [
            'date' => [
                'required'   => 'A data é obrigatória.',
                'valid_date' => 'Informe uma data válida (YYYY-MM-DD).',
            ],
            'start_time' => [
                'required'    => 'O horário de início é obrigatório.',
                'regex_match' => 'Informe um horário válido (HH:MM).',
            ],
            'end_time' => [
                'regex_match' => 'Informe um horário válido (HH:MM).',
            ],
            'doors_open_time' => [
                'regex_match' => 'Informe um horário válido (HH:MM).',
            ],
        ];
    }
}
