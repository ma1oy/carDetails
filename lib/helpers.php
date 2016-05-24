<?php

function file_get_contents_repeat($url) {
  $out = file_get_contents($url);
  if (!$out) file_get_contents_repeat($url);
  return $out;
}

function getYears($str) {
  $arr = explode('-', trim($str));
//  return [intval(strval($arr[0])), intval(strval($arr[1]))];
  return [trim($arr[0]), trim($arr[1])];
}

function addNode($pNode, $categoriesMatches, &$categoriesArray) {
  foreach ($categoriesMatches as $categoryMatch) {
    $category = explode(',', $categoryMatch);
    if ($category[1] == $pNode[0]) {
      $categoriesArray[] = $category;

      $lastIndex = count($categoriesArray) - 1;
      $categoriesArray[$lastIndex][0] -= 10001;
      $categoriesArray[$lastIndex][1] -= 10001;

      addNode($category, $categoriesMatches, $categoriesArray);
    }
  }
  return $categoriesArray;
}

function getCategories($script) {
  $categoriesArray = [];
  $categoriesMatches = [];
  preg_match_all('/(?<=\.add\()(.+?)(?=\)\;)/', $script, $categoriesMatches);
//  preg_match_all('/(?<=.add\()(.+?)(?=(,\'\',)|(,\'&nbsp;\'\);))/', $script, $categoriesMatches);

  return addNode([10001, -1, 0, 0, 0, 0], $categoriesMatches[0], $categoriesArray);
//  print_r(addNode([10001, -1, 0, 0, 0, 0], $categoriesMatches[0], $categoriesArray));
}

function getAttributes($attributes) {
//  preg_match('/~Цена/ui', $secondPart, $secondPart);
//  preg_match('/(.+?)&Ц/u', $secondPart, $secondPart);
  if ($attributes) {
    $out = [];
    $attributes = explode('Цена', $attributes)[0];
    $secondPartArr = explode(PHP_EOL, trim($attributes));
    foreach ($secondPartArr as $item) {
      $parts = explode(':', $item);
      $out[0][] = trim($parts[0]);
      $out[1][] = trim($parts[1]);
    }
    return $out;
  }
  return false;
}

function getVendor($attributes) {
    return explode(':', explode(PHP_EOL, trim($attributes))[0])[1];
}

function url($abs, $rel) {
  if ($rel) {
    if (substr($rel, 0, 4) == 'http') {
      return $rel;
    }
    return $abs . $rel;
  }
  return '';
}
