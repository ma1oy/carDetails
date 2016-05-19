<?php header('Access-Control-Allow-Origin: *'); ?>
<?php
//header('Content-Type: text/html; charset=utf-8');
//$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

require_once 'vendor/autoload.php';
require_once('db.php');

$url = 'http://klan.com.ua';

$db = DB::getInstance();

$url = 'http://klan.com.ua/cars/acura/acura_mdx_yd1_3.5_privod_na_vse_kolesa_3.5_privod_na_vse_kolesa-6092-22160/';
$html = file_get_contents($url);
phpQuery::newDocument($html);

$script = pq('body > div.page-container > div.page-wrap > section div.all-table div.left-table > div.dtree_hd + script')->text();

//file_put_contents('temp.js', file_get_contents('js/dtree.js'));
//file_put_contents('temp.js', $script, FILE_APPEND | LOCK_EX);
//file_put_contents('temp.js', file_get_contents('script.js'), FILE_APPEND | LOCK_EX);
//echo exec('nodejs temp');
// start: .add(
// end  : ,'',


?>
<script src="temp.js"></script>
<?php

getCategories($script);

phpQuery::unloadDocuments();
exit();

echo exec('nodejs script');
exit();

$html = file_get_contents($url);
phpQuery::newDocument($html);

$brands = pq('.klan-brands-block')->children('.item');

$modelsCount = 0;
$modificationsCount = 0;

foreach ($brands as $brandIndex => $brand) {
  $brandReferenceTag = pq($brand)->children('a');

  echo 'BRAND: ' . $brandReferenceTag->text() . PHP_EOL;
  $db->save('car_brands', [
    'id'    => $brandIndex,
    'name'  => trim($brandReferenceTag->text())
  ]);

  $modelGroupsHTML = phpQuery::newDocument(file_get_contents($url . $brandReferenceTag->attr('href')));
  $modelGroups = $modelGroupsHTML->find('form#table_form > table.row_model_group_table')->children('tr[id*="tr"]');

  foreach ($modelGroups as $modelGroupIndex => $modelGroup) {
    $modelGroupCell = pq($modelGroup)->children('td');
    $modelReferenceTag = $modelGroupCell->eq(1)->children('a');
    $modelsHTML = phpQuery::newDocument(file_get_contents($url . $modelReferenceTag->attr('href')/* . '/?data[is_actual]=0' */));
    $models = $modelsHTML->find('form#table_form > table.row_model_table')->children('tr[id*="tr"]');

    foreach ($models as $modelIndex => $model) {
      $modelCell = pq($model)->children('td');
      $model_years = getYears($modelCell->eq(2)->text());
      $modificationReferenceTag = $modelCell->eq(1)->children('a');

      echo 'MODEL: ' . $modificationReferenceTag->text() . PHP_EOL;
      $db->save('car_models', [
        'id'                    => $modelsCount + $modelIndex,
        'name'                  => trim($modificationReferenceTag->text()),
        'image_src'             => $url . $modelCell->eq(0)->children('a')->children('img')->attr('src'),
        'year_manufacture'      => $model_years[0],
        'year_end_manufacture'  => $model_years[1],
        'brand_id'              => $brandIndex
      ]);

      $modificationsHTML = phpQuery::newDocument(file_get_contents($url . $modificationReferenceTag->attr('href')));
      $modifications = $modificationsHTML->find('form#table_form > table.row_modeldetail_table')->children('tr[id*="tr"]');


      foreach ($modifications as $modificationIndex => $modification) {
        $modificationCell = pq($modification)->children('td');
        $modification_years = getYears($modificationCell->eq(1)->text());
        $categoriesReferenceTag = $modificationCell->eq(0)->children('a');

        echo 'MODIFICATION: ' . $categoriesReferenceTag->text() . PHP_EOL;
        $db->save('car_modifications', [
          'id'                    => $modificationsCount + $modificationIndex,
          'name'                  => trim($categoriesReferenceTag->text()),
          'car_model_id'          => $modelsCount + $modelIndex,
          'year_manufacture'      => $modification_years[0],
          'year_end_manufacture'  => $modification_years[1]
        ]);

        $categoriesHTML = phpQuery::newDocument(file_get_contents($url . $categoriesReferenceTag->attr('href')));
        $categories = $categoriesHTML->find('.all-table .left-table > .dtree > .clip');

//        function getCategories($category) {
//          $category->children('.dTreeNode');
//        }

      }

      $modificationsCount += count($modifications);
    }

    $modelsCount += count($models);
  }

  if ($brandIndex == 2) exit();
//  exit();
}

exit();

phpQuery::unloadDocuments();
