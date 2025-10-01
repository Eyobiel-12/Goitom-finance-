<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\User;

final class ClientDuplicateDetectionService
{
    /**
     * Check for potential duplicate clients
     */
    public function findDuplicates(User $user, array $clientData): array
    {
        $duplicates = [];
        
        // Check by email
        if (!empty($clientData['email'])) {
            $emailMatch = Client::where('user_id', $user->id)
                ->where('email', $clientData['email'])
                ->first();
            
            if ($emailMatch) {
                $duplicates[] = [
                    'type' => 'email',
                    'client' => $emailMatch,
                    'confidence' => 100,
                    'reason' => 'Identiek email adres',
                ];
            }
        }

        // Check by VAT number
        if (!empty($clientData['vat_number'])) {
            $vatMatch = Client::where('user_id', $user->id)
                ->where('vat_number', $clientData['vat_number'])
                ->first();
            
            if ($vatMatch) {
                $duplicates[] = [
                    'type' => 'vat',
                    'client' => $vatMatch,
                    'confidence' => 100,
                    'reason' => 'Identiek BTW nummer',
                ];
            }
        }

        // Check by name similarity
        if (!empty($clientData['name'])) {
            $nameMatches = $this->findSimilarNames($user, $clientData['name']);
            $duplicates = array_merge($duplicates, $nameMatches);
        }

        // Check by phone number
        if (!empty($clientData['phone'])) {
            $phoneMatch = Client::where('user_id', $user->id)
                ->where('phone', $clientData['phone'])
                ->first();
            
            if ($phoneMatch) {
                $duplicates[] = [
                    'type' => 'phone',
                    'client' => $phoneMatch,
                    'confidence' => 90,
                    'reason' => 'Identiek telefoonnummer',
                ];
            }
        }

        // Remove duplicates and sort by confidence
        $uniqueDuplicates = $this->removeDuplicateEntries($duplicates);
        usort($uniqueDuplicates, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $uniqueDuplicates;
    }

    /**
     * Find clients with similar names
     */
    private function findSimilarNames(User $user, string $name): array
    {
        $clients = Client::where('user_id', $user->id)->get();
        $similar = [];

        foreach ($clients as $client) {
            $similarity = $this->calculateNameSimilarity($name, $client->name);
            
            if ($similarity >= 80) {
                $similar[] = [
                    'type' => 'name',
                    'client' => $client,
                    'confidence' => $similarity,
                    'reason' => "Gelijkaardige naam ({$similarity}% match)",
                ];
            }
        }

        return $similar;
    }

    /**
     * Calculate name similarity percentage
     */
    private function calculateNameSimilarity(string $name1, string $name2): int
    {
        $name1 = strtolower(trim($name1));
        $name2 = strtolower(trim($name2));

        // Exact match
        if ($name1 === $name2) {
            return 100;
        }

        // Use Levenshtein distance
        $maxLength = max(strlen($name1), strlen($name2));
        if ($maxLength === 0) {
            return 0;
        }

        $distance = levenshtein($name1, $name2);
        $similarity = (($maxLength - $distance) / $maxLength) * 100;

        return (int) round($similarity);
    }

    /**
     * Remove duplicate entries (same client appearing multiple times)
     */
    private function removeDuplicateEntries(array $duplicates): array
    {
        $seen = [];
        $unique = [];

        foreach ($duplicates as $duplicate) {
            $clientId = $duplicate['client']->id;
            
            if (!isset($seen[$clientId])) {
                $seen[$clientId] = true;
                $unique[] = $duplicate;
            }
        }

        return $unique;
    }

    /**
     * Suggest merge for duplicate clients
     */
    public function suggestMerge(array $duplicates): array
    {
        if (count($duplicates) < 2) {
            return [];
        }

        $primary = $duplicates[0]['client'];
        $secondary = $duplicates[1]['client'];

        $suggestions = [
            'primary_client' => $primary,
            'secondary_client' => $secondary,
            'merge_data' => [
                'name' => $primary->name ?: $secondary->name,
                'email' => $primary->email ?: $secondary->email,
                'phone' => $primary->phone ?: $secondary->phone,
                'vat_number' => $primary->vat_number ?: $secondary->vat_number,
                'address' => $primary->address ?: $secondary->address,
                'city' => $primary->city ?: $secondary->city,
                'postal_code' => $primary->postal_code ?: $secondary->postal_code,
                'country' => $primary->country ?: $secondary->country,
                'notes' => trim(($primary->notes ?: '') . "\n" . ($secondary->notes ?: '')),
            ],
        ];

        return $suggestions;
    }

    /**
     * Get duplicate statistics for a user
     */
    public function getDuplicateStats(User $user): array
    {
        $clients = Client::where('user_id', $user->id)->get();
        $duplicateGroups = [];
        $processed = [];

        foreach ($clients as $client) {
            if (in_array($client->id, $processed)) {
                continue;
            }

            $group = [$client];
            $processed[] = $client->id;

            // Find duplicates for this client
            foreach ($clients as $otherClient) {
                if ($otherClient->id === $client->id || in_array($otherClient->id, $processed)) {
                    continue;
                }

                $duplicates = $this->findDuplicates($user, [
                    'name' => $otherClient->name,
                    'email' => $otherClient->email,
                    'vat_number' => $otherClient->vat_number,
                    'phone' => $otherClient->phone,
                ]);

                if (!empty($duplicates)) {
                    $group[] = $otherClient;
                    $processed[] = $otherClient->id;
                }
            }

            if (count($group) > 1) {
                $duplicateGroups[] = $group;
            }
        }

        return [
            'total_clients' => $clients->count(),
            'duplicate_groups' => count($duplicateGroups),
            'potential_duplicates' => array_sum(array_map('count', $duplicateGroups)),
            'groups' => $duplicateGroups,
        ];
    }
}
