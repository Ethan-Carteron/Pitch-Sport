<?php

namespace App\Service;

/**
 * Service dédié aux conseils textuels basés sur les niveaux d'alerte d'une joueuse.
 *
 * Structure de retour de getAdvices() :
 * Chaque élément est un tableau associatif :
 *   - 'metric'  : string (ex : 'acwr', 'vmax', 'foster')
 *   - 'level'   : int    (0 = vert, 1 = orange, 2 = rouge)
 *   - 'message' : string (phrase de conseil)
 */
class AlertAdviceService
{
    /**
     * @return array<array{metric: string, level: int, message: string}>
     */
    public function getAdvices(?float $acwr, ?float $vmaxDrop, ?float $foster): array
    {
        // TODO: Implémenter les phrases de conseil pré-faites
        return [];
    }
}
