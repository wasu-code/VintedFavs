<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SearchController extends VintedController
{
    public function default(Request $request, Response $response, array $args)
    {
        // Inicjalizacja sesji
        session_start();

        // Pobieranie parametrów z zapytania
        $currentPage = $request->getQueryParam('page') ?? 1;
        $sort = $request->getQueryParam('sort');
        $sort = $request->getQueryParam('sort');
        if ($sort && $sort != 'fav') {
          $args['order'] = $sort; //relevance; newest_first; price_low_to_high; price_high_to_low
        }
        $query = $args['query'];

        // Sprawdzenie, czy zmieniono sortowanie lub frazę wyszukiwania
        $sortChanged = $sort !== $_SESSION['current_sort'] ?? null;
        $queryChanged = $query !== $_SESSION['current_query'] ?? null;

        // Jeśli zmieniono sortowanie lub frazę wyszukiwania, pobierz nowe wyniki
        if ($sortChanged || $queryChanged) {
            $_SESSION['current_sort'] = $sort;
            $_SESSION['current_query'] = $query;

            // Sortowanie po ulubionych
            if ($sort == 'fav') {
                $_SESSION['items'] = $this->getFavoriteItems($query);
            } else {
                // Wyszukiwanie z uwzględnieniem frazy
                $vinted = $this->search($request, $response, $args);
                $_SESSION['items'] = $vinted['items'];
            }
        }

        // Paginacja wyników
        $perPage = 46;
        $startIndex = ($currentPage - 1) * $perPage;
        $pagedItems = array_slice($_SESSION['items'], $startIndex, $perPage);

        // Wyświetlanie wyników
        return $this->render($response, 'search.html', [
            'items' => $pagedItems,
            'query' => $query,
            'page' => $currentPage ?? 1
        ]);
    }

    public function homepage(Request $request, Response $response)
    {
        // Inicjalizacja sesji
        session_start();

        // Wyświetlanie strony domowej
        return $this->render($response, 'search.html', [
            'items' => $_SESSION['items'] ?? [],
            'page' => $_SESSION['current_page'] ?? 1
        ]);
    }

    private function getFavoriteItems($query)
    {
        $cookie = $this->get_cookie("https://www.vinted.pl/");
      
        $data = json_decode($this->get_web_page('https://www.vinted.pl/api/v2/catalog/items?order=favourite_count&per_page=900&search_text=' . $query, '_vinted_fr_session=' . $cookie), true);
        if (!isset($data['items'])) {
            return [];
        }

        $items = $data['items'];

        $page = 2; // Początkowa strona (pierwsza została już pobrana)
        $maxPages = $data['pagination']['total_pages'];

        // Pobieranie kolejnych stron, ale nie więcej niż 5, aby uniknąć zbyt wielu zapytań
        while ($page <= $maxPages && $page <= 5) {
            $data = json_decode($this->get_web_page("https://www.vinted.pl/api/v2/catalog/items?order=favourite_count&per_page=900&page={$page}&q={$query}", '_vinted_fr_session=' . $cookie), true);

            if (!isset($data['items'])) {
                break;
            }

            $items = array_merge($items, $data['items']);
            $page++;
        }

        // Sortowanie po ulubionych
        usort($items, function ($a, $b) {
            return $b["favourite_count"] - $a["favourite_count"];
        });

        return $items;
    }
}
