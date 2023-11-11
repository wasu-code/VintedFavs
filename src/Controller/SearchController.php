<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SearchController extends VintedController
{
    public function default(Request $request, Response $response, array $args)
    {
        // Pobieranie parametrów z zapytania
        $currentPage = $request->getQueryParam('page');
        $sort = $request->getQueryParam('sort');
        $query = $args['query'];

        // Domyślne ustawienia
        $args['cnt'] = $currentPage ?? 1;
        $args['order'] = $sort && $sort !== 'fav' ? $sort : 'relevance';

        // Sortowanie po ulubionych
        if ($sort == 'fav') {
            $items = $this->getFavoriteItems($query);
        } else {
            // Wyszukiwanie z uwzględnieniem frazy
            $vinted = $this->search($request, $response, $args);
            $items = $vinted['items'];
        }

        // Wyświetlanie wyników
        return $this->render($response, 'search.html', [
            'items' => array_slice($items, 0, 46), // Wybieranie 46 pierwszych elementów
            'query' => $query,
            'page' => $currentPage ?? 1
        ]);
    }

    public function homepage(Request $request, Response $response)
    {
        // Wyświetlanie strony domowej
        return $this->render($response, 'search.html', []);
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