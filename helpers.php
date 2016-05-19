<?php

function getYears($str) {
  $arr = explode('-', trim($str));
//  return [intval(strval($arr[0])), intval(strval($arr[1]))];
  return [trim($arr[0]), trim($arr[1])];
}

function getCategories($script) {
  $categoriesArray = [];
  $categoriesMatches = [];
  preg_match_all('/(?<=.add\()(.+?)(?=\);)/', $script, $categoriesMatches);
//  preg_match_all('/(?<=.add\()(.+?)(?=(,\'\',)|(,\'&nbsp;\'\);))/', $script, $categoriesMatches);

  function addNode($pNode, $categoriesMatches, $categoriesArray) {
//    global $categoriesArray;
    foreach ($categoriesMatches as $categoryMatch) {
      $category = explode(',', $categoryMatch);
      $haveChildren = false;
      if ($category[1] == $pNode[0]) {
        foreach ($categoriesMatches as $categoriesMatch) {
//          if ($categoriesMatch[1] == $category[0]) $category[4] = true;
          if ($categoriesMatch[1] == $category[1]) break(2);
        }
//        if ($category[4]) addNode($category, $categoriesMatches);
        addNode($category, $categoriesMatches, $categoriesArray);
        $categoriesArray[] = $category;
      }
    }
    return $categoriesArray;
  }
  
  return addNode([10001, -1, 0, 0, 0, 0], $categoriesMatches[0], $categoriesArray);
//  print_r(addNode([10001, -1, 0, 0, 0, 0], $categoriesMatches[0]));
}
