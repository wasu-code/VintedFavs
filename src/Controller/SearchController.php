<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class SearchController extends VintedController
{
  public function default(Request $request, Response $response, array $args)
  {
    // $page = $request->getQueryParam('page');
    // if ($page) {$args.array_push('cnt' => $page);}

    $vinted = $this->search($request, $response, $args);
    $items = $vinted['items'];

    $sort = $request->getQueryParam('sort');
    //sortowanie na podstawie ?sort 
    // ...

    switch($sort) {
      case "price":
        usort($items, function($a, $b) {
          if ($a["price"] == $b["price"]) {
            return 0;
          }
          return ($a["price"] < $b["price"]) ? -1 : 1;
        });
        break;

      case "nprice":
        usort($items, function($a, $b) {
          if ($a["price"] == $b["price"]) {
            return 0;
          }
          return ($a["price"] > $b["price"]) ? -1 : 1;
        });
        break;

      case "fav":
        usort($items, function($a, $b) {
          if ($a["favourite_count"] == $b["favourite_count"]) {
            return 0;
          }
          return ($a["favourite_count"] > $b["favourite_count"]) ? -1 : 1;
        });
        break;
    }

    return $this->render($response, 'search.html', [  // render response using 'search.html' template with data of 'items'
      'items' => $items,
      'query' => $args['query']
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
