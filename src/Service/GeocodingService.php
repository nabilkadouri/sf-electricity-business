<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface; 

/**
 * Service pour interroger l'API de géocodage Nominatim d'OpenStreetMap.
 *
 * Pour une utilisation en production, considérez:
 * - La gestion des limites de requêtes (rate limiting) de l'API externe (Nominatim a des limites de 1 requête/seconde et des politiques d'utilisation).
 * - Des stratégies de cache pour éviter des appels API répétés pour les mêmes adresses.
 * - Des messages d'erreur plus granulaires pour l'utilisateur.
 * - L'ajout d'une implémentation de "retry" en cas d'échec temporaire.
 */
class GeocodingService
{
    private const NOMINATIM_API_URL = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger 
    ) {}

    /**
     * Récupère les coordonnées (latitude, longitude) pour une adresse donnée en utilisant Nominatim (OSM).
     *
     * @param string $address L'adresse complète (ex: "28 rue du garage, 69001 Lyon")
     * @return array|null Un tableau ['latitude' => float, 'longitude' => float] ou null si non trouvé/erreur.
     */
    public function getCoordinates(string $address): ?array
    {
        // Si l'adresse est vide, loggez un avertissement et retournez null immédiatement.
        if (empty($address)) {
            $this->logger->warning('GeocodingService: Adresse vide fournie pour le géocodage Nominatim.');
            return null;
        }

        try {
            // Effectue la requête GET vers l'API Nominatim.
            $response = $this->httpClient->request('GET', self::NOMINATIM_API_URL, [
                'query' => [
                    'q' => $address,         // L'adresse à rechercher
                    'format' => 'json',      // Format de réponse JSON
                    'limit' => 1,            // Ne renvoyer qu'un seul résultat (le plus pertinent)
                    'addressdetails' => 0    // Ne pas inclure les détails de l'adresse pour une réponse plus légère
                ],
                // Il est important de fournir un User-Agent pour Nominatim.
                // Indiquez le nom de votre application et une adresse email de contact.
                'headers' => [
                    'User-Agent' => 'YourAppName/1.0 (your_email@example.com)' // Mettez votre nom d'app et votre email
                ]
            ]);

            $statusCode = $response->getStatusCode(); // Récupère le code de statut HTTP de la réponse
            $content = $response->toArray();          // Désérialise le contenu JSON de la réponse en tableau PHP

            // Vérifie si la requête a réussi (code 200) et si des résultats ont été trouvés.
            if ($statusCode === 200 && !empty($content)) {
                $result = $content[0]; // Prend le premier résultat (le plus pertinent)
                return [
                    'latitude' => (float) $result['lat'],  // Convertit la latitude en float
                    'longitude' => (float) $result['lon'] // Convertit la longitude en float
                ];
            }

            // Log un avertissement si l'API a retourné une erreur ou aucun résultat.
            $this->logger->warning('GeocodingService: Aucun résultat ou erreur API Nominatim.', [
                'address' => $address,
                'status_code' => $statusCode,
                'response_content' => $content
            ]);

        } catch (\Exception $e) {
            // Log une erreur si une exception survient pendant l'appel HTTP (ex: problème réseau).
            $this->logger->error('GeocodingService: Erreur lors de l\'appel API Nominatim: ' . $e->getMessage(), [
                'address' => $address,
                'exception' => $e
            ]);
        }

        // Retourne null si aucune coordonnée n'a pu être récupérée.
        return null;
    }
}