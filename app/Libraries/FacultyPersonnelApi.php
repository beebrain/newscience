<?php

namespace App\Libraries;

use Config\ResearchApi;

/**
 * Fetches faculty personnel from the external Research Record API.
 * Used by FacultyPersonnelController (JSON response) and Pages::personnel() (view data).
 */
class FacultyPersonnelApi
{
    /**
     * Fetch faculty personnel from the external API.
     *
     * @return array|null Decoded response with 'success', 'faculty', 'personnel', 'total', 'retrieved_at'; null if not configured or request failed
     */
    public static function fetch(): ?array
    {
        $researchApi = config(ResearchApi::class);

        if (! $researchApi->isConfigured()) {
            return null;
        }

        $query = [];
        if ($researchApi->facultyId !== null) {
            $query['faculty_id'] = $researchApi->facultyId;
        } else {
            $query['faculty_code'] = $researchApi->facultyCode;
        }

        $url = $researchApi->baseUrl . '/api/public/faculty-personnel?' . http_build_query($query);

        $client   = service('curlrequest', ['timeout' => 10]);
        $response = $client->get($url, [
            'headers' => [
                'X-API-KEY' => $researchApi->apiKey,
                'Accept'    => 'application/json',
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $body       = $response->getBody();
        $data       = json_decode($body, true);

        if ($statusCode >= 200 && $statusCode < 300 && is_array($data) && ! empty($data['success'])) {
            return $data;
        }

        return null;
    }

    /**
     * Fetch and group personnel by curriculum.
     *
     * @return array{groups: array<string, array>, faculty: array|null, total: int} ['groups' => [ 'หลักสูตร X' => [ person, ... ], ... ], 'faculty' => ..., 'total' => N ]
     */
    public static function fetchGroupedByCurriculum(): array
    {
        $data = self::fetch();

        $result = [
            'groups' => [],
            'faculty' => null,
            'total'   => 0,
        ];

        if ($data === null || empty($data['personnel']) || ! is_array($data['personnel'])) {
            return $result;
        }

        $result['faculty'] = $data['faculty'] ?? null;
        $result['total']  = (int) ($data['total'] ?? 0);

        foreach ($data['personnel'] as $person) {
            $curriculum = isset($person['curriculum']) && (string) $person['curriculum'] !== ''
                ? trim((string) $person['curriculum'])
                : 'อื่นๆ';

            if (! isset($result['groups'][$curriculum])) {
                $result['groups'][$curriculum] = [];
            }
            $result['groups'][$curriculum][] = $person;
        }

        return $result;
    }
}
