<?php
//header('Content-Type: text/html; charset=utf-8');
//$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");

require_once 'vendor/autoload.php';
require_once('db.php');

$url = 'http://klan.com.ua';

$db = DB::getInstance();

//$url = 'http://klan.com.ua/cars/acura/acura_mdx_yd1_3.5_privod_na_vse_kolesa_3.5_privod_na_vse_kolesa-6092-22160/';
//$html = file_get_contents($url);
//phpQuery::newDocument($html);
//$script = pq('body > div.page-container > div.page-wrap > section div.all-table div.left-table > div.dtree_hd + script')->text();
//getCategories($script);
//phpQuery::unloadDocuments();
//exit();

////$attributesContent = file_get_contents('http://klan.com.ua/buy/ajusa_10112400_2683752/');
////$attributesContent = file_get_contents('http://klan.com.ua/buy/payen_pa970_2675881/');
//$attributesContent = file_get_contents('http://klan.com.ua/buy/nissan_1520865f0c_851/');
//$attributesHTML = phpQuery::newDocument($attributesContent);
//$attributesCell = $attributesHTML->find('.inner > .cat-5-content > div.p-info > span:first');
//$attributesRows = $attributesCell->find('tr');
//$attributes = getAttributes($attributesRows->text());
//$attributesRows->remove();
//$attributeVendor = getVendor($attributesCell->text());
//echo $attributeVendor;
//exit();


//$t = $db->getAll('car_product_categories');
//print_r($t);
//exit();

$html = file_get_contents_repeat($url);
phpQuery::newDocument($html);

$brands = pq('.klan-brands-block')->children('.item');

$modelsCount = 0;
$modificationsCount = 0;
$productsCount = 0;
$attributesCount = 0;

foreach ($brands as $brandIndex => $brand) {
  $brandReferenceTag = pq($brand)->children('a');

  echo 'BRAND: ' . trim($brandReferenceTag->text()) . PHP_EOL;
//  $db->save('car_brands', [
//    'id'    => $brandIndex,
//    'name'  => trim($brandReferenceTag->text())
//  ]);

  $modelGroupsHTML = phpQuery::newDocument(file_get_contents_repeat($url . $brandReferenceTag->attr('href')));
  $modelGroups = $modelGroupsHTML->find('form#table_form > table.row_model_group_table')->children('tr[id*="tr"]');

  foreach ($modelGroups as $modelGroupIndex => $modelGroup) {
    $modelGroupCell = pq($modelGroup)->children('td');
    $modelReferenceTag = $modelGroupCell->eq(1)->children('a');
    $modelsHTML = phpQuery::newDocument(file_get_contents_repeat($url . $modelReferenceTag->attr('href')/* . '/?data[is_actual]=0' */));
    $models = $modelsHTML->find('form#table_form > table.row_model_table')->children('tr[id*="tr"]');

    foreach ($models as $modelIndex => $model) {
      $modelCell = pq($model)->children('td');
      $model_years = getYears($modelCell->eq(2)->text());
      $modificationReferenceTag = $modelCell->eq(1)->children('a');

      echo 'MODEL: ' . trim($modificationReferenceTag->text()) . PHP_EOL;
//      $db->save('car_models', [
//        'id'                    => $modelsCount + $modelIndex,
//        'name'                  => trim($modificationReferenceTag->text()),
//        'image_src'             => $url . $modelCell->eq(0)->children('a')->children('img')->attr('src'),
//        'year_manufacture'      => $model_years[0],
//        'year_end_manufacture'  => $model_years[1],
//        'brand_id'              => $brandIndex
//      ]);

      $modificationsHTML = phpQuery::newDocument(file_get_contents_repeat($url . $modificationReferenceTag->attr('href')));
      $modifications = $modificationsHTML->find('form#table_form > table.row_modeldetail_table')->children('tr[id*="tr"]');

      foreach ($modifications as $modificationIndex => $modification) {
        $modificationCell = pq($modification)->children('td');
        $modification_years = getYears($modificationCell->eq(1)->text());
        $categoriesReferenceTag = $modificationCell->eq(0)->children('a');

        echo 'MODIFICATION: ' . trim($categoriesReferenceTag->text()) . PHP_EOL;
//        $db->save('car_modifications', [
//          'id'                    => $modificationsCount + $modificationIndex,
//          'name'                  => trim($categoriesReferenceTag->text()),
//          'car_model_id'          => $modelsCount + $modelIndex,
//          'year_manufacture'      => $modification_years[0],
//          'year_end_manufacture'  => $modification_years[1]
//        ]);

        $categoriesHTML = phpQuery::newDocument(file_get_contents_repeat($url . $categoriesReferenceTag->attr('href')));
        $categories = getCategories($categoriesHTML->
          find('body > div.page-container > div.page-wrap > section div.all-table div.left-table > div.dtree_hd + script')->text());

        foreach ($categories as $category) {
          echo 'CATEGORY: ' . $category[2] . PHP_EOL;
          $db->save('car_product_categories', [
            'id'        => $category[0],
            'parent_id' => $category[1],
            'name'      => str_replace('\'', '', $category[2])
          ]);

          $productsHTML = phpQuery::newDocument(file_get_contents_repeat($url . str_replace('\'', '', $category[3])));
          $products = $productsHTML->find('form#table_form > table.row_part_table')->children('tr[id*="tr"');

          foreach ($products as $productIndex => $product) {
            $productCell = pq($product)->children('td');
            $attributesReferenceTag = $productCell->eq(0)->children('a');
            $productImageReference = $productCell->eq(1)->children('a')->children('img')->attr('src');

            echo 'PRODUCT: ' . trim($productCell->eq(0)->text()) . PHP_EOL;
            $db->save('car_products', [
              'id'            => $productsCount + $productIndex,
              'name'          => trim($attributesReferenceTag->text()),
              'image_src'     => $productImageReference ? ($url . $productImageReference) : '',
              'price'         => $productCell->eq(4)->children('nobr')->text(),
              'quantity'      => trim($productCell->eq(3)->text()),
              'delivery_time' => str_replace('---', '', trim($productCell->eq(2)->text())),
//              'vendor_code'   => trim($productCell->eq(0)->text()),
              'category_id'   => $category[0]
            ]);

//            $db->save('car_product_to_model', [
//              'id'              => $productsCount + $productIndex,
//              'car_product_id'  => $productsCount + $productIndex,
//              'model_id'        => $modelsCount + $modelIndex,
//              'modification_id' => $modificationsCount + $modificationIndex
//            ]);

//            $db->save('car_product_to_category', [
//              'id'          => $productsCount + $productIndex,
//              'id_product'  => $productsCount + $productIndex,
//              'id_category' => $category[0]
//            ]);

            $attributesContent = file_get_contents_repeat($url . $attributesReferenceTag->attr('href'));
//            $attributesContent = file_get_contents_repeat('http://klan.com.ua/buy/ajusa_10112400_2683752/');

            if ($attributesContent) {
              $attributesHTML = phpQuery::newDocument($attributesContent);
//              $attributes = getAttributes($attributesHTML->find('.inner>.cat-5-content>div.p-info>span:first'));
              $attributesCell = $attributesHTML->find('.inner > .cat-5-content > div.p-info > span:first');
              $attributesRows = $attributesCell->find('tr');
              $attributes = getAttributes($attributesRows->text());
              $attributesRows->remove();
              $attributeVendor = getVendor($attributesCell->text());

              $db->update('car_products', [
                'id'          => $productsCount + $productIndex,
//                'name'        => trim($attributesHTML->find('.cat-5-content > [itemprop*="name"]')->text()),
//                'image_src'   => $attributesHTML->find('.p-images > a')->children('img')->attr('src'),
                'vendor'      => $attributeVendor,
                'vendor_code' => trim(explode(':', $attributesHTML->find('.cat-5-content > .p-code')->text())[1])
              ]);

              if ($attributes) {
                foreach ($attributes[0] as $attributeIndex => $attribute) {

//                static $attributesFromDbCount = 0;
                  $attributesFromDb = $db->getAll('car_product_attributes');
                  $dbHasAttribute = false;
                  $attributeId = 0;
                  foreach ($attributesFromDb as $attributeFromDbIndex => $attributeFromDb) {
                    if ($attribute == $attributeFromDb['name']) {
                      $dbHasAttribute = true;
                      $attributeId = $attributeFromDbIndex;
                      break;
                    }
                  }
                  if (!$dbHasAttribute) {
                    $attributeId = count($attributesFromDb);

                    echo 'ATTRIBUTE: ' . trim($attribute) . PHP_EOL;
                    $db->save('car_product_attributes', [
                      'id'    => $attributeId,
                      'name'  => $attributes[0][$attributeIndex]
                    ]);
                  }

                  $db->save('car_product_attr_to_product', [
                    'id'          => $attributesCount + $attributeIndex,
                    'id_product'  => $productsCount + $productIndex,
                    'id_attr'     => $attributeId,
                    'value'       => $attributes[1][$attributeIndex]
                  ]);
                }
              }
              phpQuery::unloadDocuments($attributesHTML);

              $attributes ? $attributesCount += count($attributes[0]) : null;
            }
          }
          phpQuery::unloadDocuments($productsHTML);

          $productsCount += count($products);
        }
        phpQuery::unloadDocuments($categoriesHTML);
      }
      phpQuery::unloadDocuments($modificationsHTML);

      $modificationsCount += count($modifications);
    }
    phpQuery::unloadDocuments($modelsHTML);

    $modelsCount += count($models);
  }
  phpQuery::unloadDocuments($modelGroupsHTML);

//  if ($brandIndex == 0) {
//    phpQuery::unloadDocuments();
//    exit();
//  }
//  exit();
}
phpQuery::unloadDocuments();
//phpQuery::unloadDocuments($html);
