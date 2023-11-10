<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class SearchController extends VintedController
{
  public function default(Request $request, Response $response, array $args)
  {
    $vinted = $this->search($request, $response, $args);
    $items = $vinted['items'];

    $sort = $request->getQueryParam('sort');
    //sortowanie na podstawie ?sort 
    // ...

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
