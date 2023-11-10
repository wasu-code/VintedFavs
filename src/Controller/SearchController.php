<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class SearchController extends VintedController
{
  public function default(Request $request, Response $response, array $args)
  {
    //Get Params
    $currentPage = $request->getQueryParam('page');
    if ($currentPage) {
      $args['cnt'] = $currentPage;
    }
    $sort = $request->getQueryParam('sort');
    if ($sort && $sort != 'fav') {
      $args['order'] = $sort; //relevance; newest_first; price_low_to_high; price_high_to_low
    }

    $vinted = $this->search($request, $response, $args);
    $pagination = $vinted['pagination'];
    //var_dump($pagination);
    $items = $vinted['items'];

    if ($sort == 'fav') {
      //sortowanie po ulub
      $cookie = $this->get_cookie("https://www.vinted.pl/");
      $data = json_decode($this->get_web_page('https://www.vinted.pl/api/v2/catalog/items?order=favourite_count&per_page=900', '_vinted_fr_session=' . $cookie), true);
      $items = $data["items"];
      $page = 1;
      $max_pages = $data["pagination"]["total_pages"];

      while ($page <= $max_pages) {
        $dat = json_decode($this->get_web_page('https://www.vinted.pl/api/v2/catalog/items?order=favourite_count&per_page=900&page=' . $page, '_vinted_fr_session=' . $cookie), true);
        $page++;
        $items = array_merge($items, $dat["items"]);
      }


      print_r(count($items));
      print_r($pagination);
      // print_r($items);
      //sortowanie, które wcześniej zrobiłem po $items
      //wybranie 46 pierwszych itemów z listy
      //wyświetlenie ich
      usort($items, function ($a, $b) {
        if ($a["favourite_count"] == $b["favourite_count"]) {
          return 0;
        }
        return ($a["favourite_count"] > $b["favourite_count"]) ? -1 : 1;
      });
    }



    //sortowanie na podstawie ?sort 
    // ...

    /*switch ($sort) {
      case "price":
        usort($items, function ($a, $b) {
          if ($a["price"] == $b["price"]) {
            return 0;
          }
          return ($a["price"] < $b["price"]) ? -1 : 1;
        });
        break;

      case "nprice":
        usort($items, function ($a, $b) {
          if ($a["price"] == $b["price"]) {
            return 0;
          }
          return ($a["price"] > $b["price"]) ? -1 : 1;
        });
        break;

      case "fav":
        usort($items, function ($a, $b) {
          if ($a["favourite_count"] == $b["favourite_count"]) {
            return 0;
          }
          return ($a["favourite_count"] > $b["favourite_count"]) ? -1 : 1;
        });
        break;
    }*/

    if (!$currentPage) {
      $currentPage = 1;
    }

    return $this->render($response, 'search.html', [  // render response using 'search.html' template with data of 'items'
      'items' => $items,
      'query' => $args['query'],
      'page' => $currentPage
    ]);
  }

  public function homepage(Request $request, Response $response)
  {
    return $this->render($response, 'search.html', [  // render response using 'search.html' template with data of 'items'

    ]);
  }

  /*public function form(Request $request, Response $response)
  {
    $albums = json_decode(__DIR__ . '/../../data/red.json', true);

    $query = $request->getParam('q');
    if ($query) {
      $albums = array_values(array_filter($albums, function ($album) use ($query) {
        return strpos($album['title'], $query) !== false ||
          strpos($album['artist'], $query) !== false;
      }));
    }

    return $this->render($response, 'form.html', [
      'albums' => $albums,
      'query' => $query
    ]);
  }*/
}
