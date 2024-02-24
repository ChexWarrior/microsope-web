<?php

namespace App\Repository;

use App\Entity\History;
use App\Entity\Term;

interface TermRepositoryInterface
{
    /**
     * Find the greatest place value for children terms of parent.
     *
     * @param History|Term $parent - Parent object of term.
     * @return int - The largest place value for children terms.
     */
    public function findLastPlace(History|Term $parent): int;

    /**
     * Find all children terms of parent with a place value greater than
     * or equal to $place.
     *
     * @param int $place
     * @param History|Term $parent
     * @return array - Matching terms.
     */
    public function findAllWithPlaceGreaterThanOrEqual(int $place, History|Term $parent): array;

    /**
     * Find a term by its place value.
     * 
     * @param int $place
     * @param History|Term $parent
     * @return Term
     */
    public function findByPlace(int $place, History|Term $parent): Term;
}
