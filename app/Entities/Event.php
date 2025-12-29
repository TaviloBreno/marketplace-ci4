<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Event extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [
        'id'                       => 'integer',
        'user_id'                  => 'integer',
        'is_featured'              => 'boolean',
        'has_seat_map'             => 'boolean',
        'max_tickets_per_purchase' => 'integer',
    ];

    // Relacionamentos carregados
    protected $organizer;
    protected $days;
    protected $sectors;

    /**
     * Gera o slug a partir do título
     */
    public function setTitle(string $title): self
    {
        $this->attributes['title'] = $title;
        $this->attributes['slug']  = url_title($title, '-', true);
        return $this;
    }

    /**
     * Retorna a URL da imagem
     */
    public function getImageUrl(): string
    {
        if (empty($this->attributes['image'])) {
            return base_url('assets/images/event-placeholder.jpg');
        }
        return base_url('uploads/events/' . $this->attributes['image']);
    }

    /**
     * Retorna a URL do banner
     */
    public function getBannerUrl(): string
    {
        if (empty($this->attributes['banner'])) {
            return base_url('assets/images/banner-placeholder.jpg');
        }
        return base_url('uploads/events/' . $this->attributes['banner']);
    }

    /**
     * Verifica se o evento está publicado
     */
    public function isPublished(): bool
    {
        return $this->attributes['status'] === 'published';
    }

    /**
     * Verifica se o evento está cancelado
     */
    public function isCancelled(): bool
    {
        return $this->attributes['status'] === 'cancelled';
    }

    /**
     * Retorna o endereço completo do local
     */
    public function getFullAddress(): string
    {
        return sprintf(
            '%s - %s, %s - %s',
            $this->attributes['venue_address'],
            $this->attributes['venue_city'],
            $this->attributes['venue_state'],
            $this->attributes['venue_zipcode']
        );
    }

    /**
     * Retorna as categorias disponíveis
     */
    public static function getCategories(): array
    {
        return [
            'show'       => 'Show/Concerto',
            'theater'    => 'Teatro',
            'sports'     => 'Esportes',
            'festival'   => 'Festival',
            'conference' => 'Conferência',
            'workshop'   => 'Workshop',
            'party'      => 'Festa',
            'exhibition' => 'Exposição',
            'other'      => 'Outros',
        ];
    }

    /**
     * Retorna o nome da categoria
     */
    public function getCategoryName(): string
    {
        $categories = self::getCategories();
        return $categories[$this->attributes['category']] ?? $this->attributes['category'];
    }

    /**
     * Retorna o badge de status
     */
    public function getStatusBadge(): string
    {
        $badges = [
            'draft'     => '<span class="badge bg-secondary">Rascunho</span>',
            'published' => '<span class="badge bg-success">Publicado</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelado</span>',
            'finished'  => '<span class="badge bg-dark">Finalizado</span>',
        ];
        return $badges[$this->attributes['status']] ?? '';
    }
}
