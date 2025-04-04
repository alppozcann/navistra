<?php

namespace App\Services;

use App\Models\GemiRoute;
use App\Models\Yuk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ShipCargoMatchingService
{
    /**
     * Find matching cargo for a specific ship route
     *
     * @param GemiRoute $gemiRoute
     * @return Collection
     */
    public function findMatchingCargoForShip(GemiRoute $gemiRoute): Collection
    {
        // Basic matching criteria
        $query = Yuk::where('status', 'active')
            ->where('from_location', $gemiRoute->start_location)
            ->where('to_location', $gemiRoute->end_location)
            ->where('weight', '<=', $gemiRoute->available_capacity)
            ->where('desired_delivery_date', '>=', $gemiRoute->departure_date);

        // Get matching cargo
        $matchingCargo = $query->get();

        // Calculate match score for each cargo
        $matchingCargo->transform(function ($cargo) use ($gemiRoute) {
            $cargo->match_score = $this->calculateMatchScore($cargo, $gemiRoute);
            return $cargo;
        });

        // Sort by match score (highest first)
        return $matchingCargo->sortByDesc('match_score');
    }

    /**
     * Find matching ships for a specific cargo
     *
     * @param Yuk $yuk
     * @return Collection
     */
    public function findMatchingShipsForCargo(Yuk $yuk): Collection
    {
        // Basic matching criteria
        $query = GemiRoute::where('status', 'active')
            ->where('start_location', $yuk->from_location)
            ->where('end_location', $yuk->to_location)
            ->where('available_capacity', '>=', $yuk->weight)
            ->where('departure_date', '<=', $yuk->desired_delivery_date);

        // Get matching ships
        $matchingShips = $query->get();

        // Calculate match score for each ship
        $matchingShips->transform(function ($ship) use ($yuk) {
            $ship->match_score = $this->calculateMatchScore($yuk, $ship);
            return $ship;
        });

        // Sort by match score (highest first)
        return $matchingShips->sortByDesc('match_score');
    }

    /**
     * Calculate a match score between a cargo and a ship
     * Higher score means better match
     *
     * @param Yuk $yuk
     * @param GemiRoute $gemiRoute
     * @return float
     */
    private function calculateMatchScore(Yuk $yuk, GemiRoute $gemiRoute): float
    {
        $score = 0;
        
        // Location match (40% of total score)
        $locationScore = $this->calculateLocationMatchScore($yuk, $gemiRoute);
        $score += $locationScore * 0.4;
        
        // Capacity utilization (20% of total score)
        $capacityScore = $this->calculateCapacityScore($yuk, $gemiRoute);
        $score += $capacityScore * 0.2;
        
        // Time compatibility (20% of total score)
        $timeScore = $this->calculateTimeScore($yuk, $gemiRoute);
        $score += $timeScore * 0.2;
        
        // Price compatibility (20% of total score)
        $priceScore = $this->calculatePriceScore($yuk, $gemiRoute);
        $score += $priceScore * 0.2;
        
        return $score;
    }

    /**
     * Calculate location match score
     *
     * @param Yuk $yuk
     * @param GemiRoute $gemiRoute
     * @return float
     */
    private function calculateLocationMatchScore(Yuk $yuk, GemiRoute $gemiRoute): float
    {
        $score = 0;
        
        // Perfect match for origin and destination
        if ($yuk->from_location === $gemiRoute->start_location && 
            $yuk->to_location === $gemiRoute->end_location) {
            return 1.0;
        }
        
        // Check if origin is on the ship's route
        if ($yuk->from_location === $gemiRoute->start_location) {
            $score += 0.5;
        } elseif ($gemiRoute->way_points && in_array($yuk->from_location, $gemiRoute->way_points)) {
            $score += 0.3;
        }
        
        // Check if destination is on the ship's route
        if ($yuk->to_location === $gemiRoute->end_location) {
            $score += 0.5;
        } elseif ($gemiRoute->way_points && in_array($yuk->to_location, $gemiRoute->way_points)) {
            $score += 0.3;
        }
        
        return $score;
    }

    /**
     * Calculate capacity utilization score
     *
     * @param Yuk $yuk
     * @param GemiRoute $gemiRoute
     * @return float
     */
    private function calculateCapacityScore(Yuk $yuk, GemiRoute $gemiRoute): float
    {
        // Calculate capacity utilization percentage
        $utilization = $yuk->weight / $gemiRoute->available_capacity;
        
        // Ideal utilization is between 70% and 90%
        if ($utilization >= 0.7 && $utilization <= 0.9) {
            return 1.0;
        } elseif ($utilization >= 0.5 && $utilization < 0.7) {
            return 0.8;
        } elseif ($utilization > 0.9 && $utilization <= 1.0) {
            return 0.6;
        } elseif ($utilization >= 0.3 && $utilization < 0.5) {
            return 0.4;
        } else {
            return 0.2;
        }
    }

    /**
     * Calculate time compatibility score
     *
     * @param Yuk $yuk
     * @param GemiRoute $gemiRoute
     * @return float
     */
    private function calculateTimeScore(Yuk $yuk, GemiRoute $gemiRoute): float
    {
        // Calculate days between departure and desired delivery
        $departureDate = $gemiRoute->departure_date;
        $arrivalDate = $gemiRoute->arrival_date;
        $desiredDeliveryDate = $yuk->desired_delivery_date;
        
        // If ship arrives before desired delivery date, that's good
        if ($arrivalDate <= $desiredDeliveryDate) {
            // Calculate how much buffer time we have
            $bufferDays = $desiredDeliveryDate->diffInDays($arrivalDate, false);
            
            // More buffer time is better, up to a point
            if ($bufferDays >= 7) {
                return 1.0;
            } elseif ($bufferDays >= 3) {
                return 0.8;
            } elseif ($bufferDays >= 1) {
                return 0.6;
            } else {
                return 0.4;
            }
        } else {
            // Ship arrives after desired delivery date
            $delayDays = $desiredDeliveryDate->diffInDays($arrivalDate, false);
            
            // Less delay is better
            if ($delayDays <= 1) {
                return 0.3;
            } elseif ($delayDays <= 3) {
                return 0.2;
            } else {
                return 0.1;
            }
        }
    }

    /**
     * Calculate price compatibility score
     *
     * @param Yuk $yuk
     * @param GemiRoute $gemiRoute
     * @return float
     */
    private function calculatePriceScore(Yuk $yuk, GemiRoute $gemiRoute): float
    {
        // Calculate price per kg for both cargo and ship
        $cargoPricePerKg = $yuk->proposed_price / $yuk->weight;
        $shipPricePerKg = $gemiRoute->price / $gemiRoute->available_capacity;
        
        // Calculate price ratio (cargo price / ship price)
        $priceRatio = $cargoPricePerKg / $shipPricePerKg;
        
        // Ideal ratio is close to 1 (cargo price matches ship price)
        if ($priceRatio >= 0.9 && $priceRatio <= 1.1) {
            return 1.0;
        } elseif ($priceRatio >= 0.7 && $priceRatio < 0.9) {
            return 0.8;
        } elseif ($priceRatio > 1.1 && $priceRatio <= 1.3) {
            return 0.6;
        } elseif ($priceRatio >= 0.5 && $priceRatio < 0.7) {
            return 0.4;
        } elseif ($priceRatio > 1.3 && $priceRatio <= 1.5) {
            return 0.3;
        } else {
            return 0.2;
        }
    }

    /**
     * Automatically match available cargo with ships
     * This method can be called by a scheduled command
     *
     * @return array
     */
    public function autoMatchCargoAndShips(): array
    {
        $matches = [];
        $matchedCargoIds = [];
        $matchedShipIds = [];
        
        // Get all active ships
        $activeShips = GemiRoute::where('status', 'active')->get();
        
        foreach ($activeShips as $ship) {
            // Skip if ship is already matched
            if (in_array($ship->id, $matchedShipIds)) {
                continue;
            }
            
            // Find matching cargo for this ship
            $matchingCargo = $this->findMatchingCargoForShip($ship);
            
            // Filter out cargo that's already matched
            $matchingCargo = $matchingCargo->filter(function ($cargo) use ($matchedCargoIds) {
                return !in_array($cargo->id, $matchedCargoIds);
            });
            
            // If we have matching cargo, take the best match
            if ($matchingCargo->isNotEmpty()) {
                $bestMatch = $matchingCargo->first();
                
                // Only consider it a match if the score is above a threshold
                if ($bestMatch->match_score >= 0.7) {
                    $matches[] = [
                        'ship' => $ship,
                        'cargo' => $bestMatch,
                        'score' => $bestMatch->match_score
                    ];
                    
                    // Mark as matched
                    $matchedCargoIds[] = $bestMatch->id;
                    $matchedShipIds[] = $ship->id;
                }
            }
        }
        
        return $matches;
    }
} 