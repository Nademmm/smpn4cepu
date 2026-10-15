<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerpusnasBookService
{
    /**
     * Cari metadata buku berdasarkan nomor ISBN (10 atau 13 digit).
     * Terintegrasi dengan katalog online dan OpenLibrary / Perpusnas ISBN service.
     *
     * @param string $isbn
     * @return array|null
     */
    public function lookupByIsbn(string $isbn): ?array
    {
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
        if (empty($cleanIsbn)) {
            return null;
        }

        try {
            // 1. Coba OpenLibrary Books API
            $response = Http::timeout(5)->get("https://openlibrary.org/api/books", [
                'bibkeys' => 'ISBN:' . $cleanIsbn,
                'format' => 'json',
                'jscmd' => 'data',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $key = 'ISBN:' . $cleanIsbn;

                if (isset($data[$key])) {
                    $book = $data[$key];

                    $authors = [];
                    if (!empty($book['authors'])) {
                        foreach ($book['authors'] as $author) {
                            $authors[] = $author['name'] ?? '';
                        }
                    }

                    $publishers = [];
                    if (!empty($book['publishers'])) {
                        foreach ($book['publishers'] as $pub) {
                            $publishers[] = $pub['name'] ?? '';
                        }
                    }

                    return [
                        'source' => 'OpenLibrary / ISBN Katalog Terpadu',
                        'isbn' => $cleanIsbn,
                        'title' => $book['title'] ?? null,
                        'author' => implode(', ', array_filter($authors)),
                        'publisher' => implode(', ', array_filter($publishers)),
                        'publish_year' => isset($book['publish_date']) ? (int) preg_replace('/[^0-9]/', '', $book['publish_date']) : null,
                        'pages' => $book['number_of_pages'] ?? null,
                        'cover_url' => $book['cover']['large'] ?? $book['cover']['medium'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Perpusnas / OpenLibrary ISBN lookup gagal: ' . $e->getMessage(), [
                'isbn' => $cleanIsbn,
            ]);
        }

        return null;
    }
}
