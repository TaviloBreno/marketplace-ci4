<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;

/**
 * EventCache - Gerenciamento de cache para eventos
 * 
 * Centraliza a lógica de cache para melhorar performance
 * nas listagens e páginas de eventos
 */
class EventCache
{
    protected CacheInterface $cache;
    
    // Prefixos de cache
    const PREFIX_EVENT = 'event_';
    const PREFIX_LISTING = 'event_listing_';
    const PREFIX_FEATURED = 'featured_events';
    const PREFIX_CITIES = 'event_cities';
    const PREFIX_STATS = 'event_stats_';
    
    // Tempos de expiração (em segundos)
    const TTL_EVENT = 300;        // 5 minutos
    const TTL_LISTING = 180;      // 3 minutos
    const TTL_FEATURED = 600;     // 10 minutos
    const TTL_CITIES = 3600;      // 1 hora
    const TTL_STATS = 300;        // 5 minutos

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    /**
     * Obter evento do cache ou executar callback
     */
    public function getEvent(int $eventId, callable $callback): mixed
    {
        $key = self::PREFIX_EVENT . $eventId;
        
        $data = $this->cache->get($key);
        
        if ($data === null) {
            $data = $callback();
            if ($data !== null) {
                $this->cache->save($key, $data, self::TTL_EVENT);
            }
        }
        
        return $data;
    }

    /**
     * Obter listagem de eventos do cache
     */
    public function getEventListing(string $cacheKey, callable $callback): mixed
    {
        $key = self::PREFIX_LISTING . md5($cacheKey);
        
        $data = $this->cache->get($key);
        
        if ($data === null) {
            $data = $callback();
            $this->cache->save($key, $data, self::TTL_LISTING);
        }
        
        return $data;
    }

    /**
     * Obter eventos em destaque
     */
    public function getFeaturedEvents(callable $callback): mixed
    {
        $data = $this->cache->get(self::PREFIX_FEATURED);
        
        if ($data === null) {
            $data = $callback();
            $this->cache->save(self::PREFIX_FEATURED, $data, self::TTL_FEATURED);
        }
        
        return $data;
    }

    /**
     * Obter lista de cidades
     */
    public function getCities(callable $callback): mixed
    {
        $data = $this->cache->get(self::PREFIX_CITIES);
        
        if ($data === null) {
            $data = $callback();
            $this->cache->save(self::PREFIX_CITIES, $data, self::TTL_CITIES);
        }
        
        return $data;
    }

    /**
     * Obter estatísticas de evento
     */
    public function getEventStats(int $eventId, callable $callback): mixed
    {
        $key = self::PREFIX_STATS . $eventId;
        
        $data = $this->cache->get($key);
        
        if ($data === null) {
            $data = $callback();
            $this->cache->save($key, $data, self::TTL_STATS);
        }
        
        return $data;
    }

    /**
     * Invalidar cache de um evento específico
     */
    public function invalidateEvent(int $eventId): void
    {
        $this->cache->delete(self::PREFIX_EVENT . $eventId);
        $this->cache->delete(self::PREFIX_STATS . $eventId);
        
        // Também invalidar listagens e featured (pois podem conter o evento)
        $this->invalidateListings();
    }

    /**
     * Invalidar todas as listagens
     */
    public function invalidateListings(): void
    {
        // Como não temos uma forma de limpar por prefixo no CI4,
        // vamos invalidar os caches conhecidos
        $this->cache->delete(self::PREFIX_FEATURED);
        $this->cache->delete(self::PREFIX_CITIES);
        
        // Para listagens com hash, o TTL curto já cuida da atualização
    }

    /**
     * Invalidar todo o cache de eventos
     */
    public function invalidateAll(): void
    {
        // Usar clean() para limpar todo o cache
        // Em produção, considere usar tags ou cache específico
        $this->cache->clean();
    }

    /**
     * Gerar chave única para listagem baseada nos parâmetros
     */
    public function generateListingKey(array $params): string
    {
        ksort($params);
        return http_build_query($params);
    }
}
