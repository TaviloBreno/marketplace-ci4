<?php

namespace App\Validation;

class EventValidation
{
    /**
     * Regras de validação para criação de evento
     */
    public static function createRules(): array
    {
        return [
            'title' => [
                'label'  => 'Título',
                'rules'  => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'min_length' => 'O {field} deve ter pelo menos {param} caracteres.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'description' => [
                'label'  => 'Descrição',
                'rules'  => 'permit_empty|max_length[10000]',
                'errors' => [
                    'max_length' => 'A {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'category' => [
                'label'  => 'Categoria',
                'rules'  => 'required|in_list[show,teatro,esporte,festival,conferencia,workshop,outros]',
                'errors' => [
                    'required' => 'A {field} é obrigatória.',
                    'in_list'  => 'Selecione uma {field} válida.',
                ],
            ],
            'venue_name' => [
                'label'  => 'Nome do local',
                'rules'  => 'required|max_length[255]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'venue_address' => [
                'label'  => 'Endereço',
                'rules'  => 'required|max_length[255]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'venue_city' => [
                'label'  => 'Cidade',
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'A {field} é obrigatória.',
                    'max_length' => 'A {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'venue_state' => [
                'label'  => 'Estado',
                'rules'  => 'required|exact_length[2]',
                'errors' => [
                    'required'     => 'O {field} é obrigatório.',
                    'exact_length' => 'O {field} deve ter exatamente {param} caracteres.',
                ],
            ],
            'venue_zip_code' => [
                'label'  => 'CEP',
                'rules'  => 'required|max_length[10]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
        ];
    }

    /**
     * Regras de validação para atualização de evento
     */
    public static function updateRules(): array
    {
        return self::createRules();
    }

    /**
     * Regras de validação para data do evento
     */
    public static function eventDayRules(): array
    {
        return [
            'date' => [
                'label'  => 'Data',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => 'A {field} é obrigatória.',
                    'valid_date' => 'A {field} deve ser uma data válida.',
                ],
            ],
            'start_time' => [
                'label'  => 'Horário de início',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'O {field} é obrigatório.',
                ],
            ],
            'doors_open' => [
                'label'  => 'Abertura dos portões',
                'rules'  => 'permit_empty',
            ],
        ];
    }

    /**
     * Regras de validação para setor
     */
    public static function sectorRules(): array
    {
        return [
            'name' => [
                'label'  => 'Nome do setor',
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'price' => [
                'label'  => 'Preço',
                'rules'  => 'required|decimal',
                'errors' => [
                    'required' => 'O {field} é obrigatório.',
                    'decimal'  => 'O {field} deve ser um valor decimal.',
                ],
            ],
            'color' => [
                'label'  => 'Cor',
                'rules'  => 'permit_empty|max_length[7]',
            ],
            'is_numbered' => [
                'label'  => 'Numerado',
                'rules'  => 'required|in_list[0,1]',
            ],
            'capacity' => [
                'label'  => 'Capacidade',
                'rules'  => 'permit_empty|integer',
            ],
        ];
    }

    /**
     * Regras de validação para fila
     */
    public static function queueRules(): array
    {
        return [
            'name' => [
                'label'  => 'Nome da fila',
                'rules'  => 'required|max_length[10]',
                'errors' => [
                    'required'   => 'O {field} é obrigatório.',
                    'max_length' => 'O {field} deve ter no máximo {param} caracteres.',
                ],
            ],
            'total_seats' => [
                'label'  => 'Total de assentos',
                'rules'  => 'required|integer|greater_than[0]',
                'errors' => [
                    'required'     => 'O {field} é obrigatório.',
                    'integer'      => 'O {field} deve ser um número inteiro.',
                    'greater_than' => 'O {field} deve ser maior que {param}.',
                ],
            ],
        ];
    }

    /**
     * Valida uma imagem de upload
     */
    public static function imageRules(): array
    {
        return [
            'image' => [
                'label'  => 'Imagem',
                'rules'  => 'permit_empty|uploaded[image]|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
                'errors' => [
                    'is_image' => 'O arquivo deve ser uma imagem.',
                    'max_size' => 'A imagem deve ter no máximo 2MB.',
                    'mime_in'  => 'O formato da imagem deve ser JPG, PNG ou WebP.',
                ],
            ],
        ];
    }

    /**
     * Valida banner de upload
     */
    public static function bannerRules(): array
    {
        return [
            'banner' => [
                'label'  => 'Banner',
                'rules'  => 'permit_empty|uploaded[banner]|is_image[banner]|max_size[banner,4096]|mime_in[banner,image/jpg,image/jpeg,image/png,image/webp]',
                'errors' => [
                    'is_image' => 'O arquivo deve ser uma imagem.',
                    'max_size' => 'O banner deve ter no máximo 4MB.',
                    'mime_in'  => 'O formato do banner deve ser JPG, PNG ou WebP.',
                ],
            ],
        ];
    }

    /**
     * Validação customizada: verifica se a data do evento é futura
     */
    public static function validateFutureDate(string $date): bool
    {
        return strtotime($date) > strtotime('today');
    }

    /**
     * Validação customizada: verifica se o horário de abertura é antes do início
     */
    public static function validateDoorsOpenBeforeStart(string $doorsOpen, string $startTime): bool
    {
        if (empty($doorsOpen)) {
            return true;
        }

        return strtotime($doorsOpen) < strtotime($startTime);
    }
}
