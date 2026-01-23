<?php

namespace App\Helpers;

class SearchHelper
{
    /**
     * Calculate similarity percentage between two strings
     * Returns a value between 0 and 100
     */
    public static function similarity($str1, $str2): float
    {
        // Normalize both strings first
        $str1 = self::normalizeForComparison($str1);
        $str2 = self::normalizeForComparison($str2);
        
        if ($str1 === $str2) {
            return 100.0;
        }
        
        if (empty($str1) || empty($str2)) {
            return 0.0;
        }
        
        // Use similar_text for better accuracy
        similar_text($str1, $str2, $percent);
        
        // Also check if one string contains the other (for partial matches)
        if (str_contains($str1, $str2) || str_contains($str2, $str1)) {
            $percent = max($percent, 75.0); // Boost partial matches
        }
        
        // Boost similarity for common variations (PT vs PT., etc.)
        if (self::areSimilarVariations($str1, $str2)) {
            $percent = max($percent, 85.0);
        }
        
        return round($percent, 2);
    }

    /**
     * Normalize string for comparison (remove special chars, normalize spaces, etc.)
     */
    private static function normalizeForComparison($str): string
    {
        $str = mb_strtolower(trim($str));
        
        // Normalize common variations
        $str = preg_replace('/\s*\.\s*/', '.', $str); // Remove spaces around dots
        $str = preg_replace('/\s+/', ' ', $str); // Multiple spaces to single space
        $str = str_replace(['pt ', 'pt.'], 'pt.', $str);
        $str = str_replace(['cv ', 'cv.'], 'cv.', $str);
        $str = str_replace(['ud ', 'ud.'], 'ud.', $str);
        
        return trim($str);
    }

    /**
     * Check if two strings are common variations of each other
     */
    private static function areSimilarVariations($str1, $str2): bool
    {
        // Check for PT variations
        $pt1 = preg_match('/\bpt\s*\.?\s*/i', $str1);
        $pt2 = preg_match('/\bpt\s*\.?\s*/i', $str2);
        if ($pt1 && $pt2) {
            $rest1 = preg_replace('/\bpt\s*\.?\s*/i', '', $str1);
            $rest2 = preg_replace('/\bpt\s*\.?\s*/i', '', $str2);
            if (trim($rest1) === trim($rest2)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Find suggestions from a collection of values
     * Returns array of suggestions with similarity scores
     */
    public static function findSuggestions($searchTerm, $values, $minSimilarity = 60.0, $maxResults = 5): array
    {
        $suggestions = [];
        
        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }
            
            $similarity = self::similarity($searchTerm, $value);
            
            if ($similarity >= $minSimilarity) {
                $suggestions[] = [
                    'value' => $value,
                    'similarity' => $similarity
                ];
            }
        }
        
        // Sort by similarity (highest first)
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Return top results
        return array_slice($suggestions, 0, $maxResults);
    }

    /**
     * Normalize search term (remove special characters, normalize spaces)
     */
    public static function normalizeSearchTerm($term): string
    {
        // Remove multiple spaces
        $term = preg_replace('/\s+/', ' ', trim($term));
        
        // Normalize common variations
        $term = str_replace(['PT ', 'PT.', 'Pt ', 'Pt.'], 'PT. ', $term);
        $term = str_replace(['CV ', 'Cv '], 'CV ', $term);
        $term = str_replace(['UD ', 'Ud '], 'UD ', $term);
        
        return $term;
    }
}





