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
        'max_tickets_per_purchase' => 'integer',
    ];

    /**
     * Gera o slug automaticamente a partir do título
     */
    public function setTitle(string $title): self
    {
        $this->attributes['title'] = $title;
        $this->attributes['slug']  = url_title($title, '-', true);
        
        return $this;
    }

    /**
     * Retorna a URL completa da imagem
     */
    public function getImageUrl(): ?string
    {
        if (empty($this->attributes['image'])) {
            return base_url('assets/images/event-placeholder.jpg');
        }
        
        return base_url('uploads/events/' . $this->attributes['image']);
    }

    /**
     * Retorna a URL completa do banner
     */
    public function getBannerUrl(): ?string
    {
        if (empty($this->attributes['banner'])) {
            return $this->getImageUrl();
        }
        
        return base_url('uploads/events/' . $this->attributes['banner']);
    }

    /**
     * Retorna o endereço completo do local
     */
    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s - %s, %s',
            $this->attributes['venue_address'],
            $this->attributes['venue_city'],
            $this->attributes['venue_state'],
            $this->attributes['venue_zip_code']
        );
    }

    /**
     * Verifica se o evento está publicado
     */
    public function isPublished(): bool
    {
        return $this->attributes['status'] === 'published';
    }

    /**
     * Verifica se o evento pode ser editado
     */
    public function canEdit(): bool
    {
        return in_array($this->attributes['status'], ['draft', 'published']);
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'draft'     => 'Rascunho',
            'published' => 'Publicado',
            'cancelled' => 'Cancelado',
            'finished'  => 'Finalizado',
        ];

        return $labels[$this->attributes['status']] ?? $this->attributes['status'];
    }

    /**
     * Retorna a classe CSS do status
     */
    public function getStatusClass(): string
    {
        $classes = [
            'draft'     => 'secondary',
            'published' => 'success',
            'cancelled' => 'danger',
            'finished'  => 'info',
        ];

        return $classes[$this->attributes['status']] ?? 'secondary';
    }
}
