<?php
require_once 'vendor/autoload.php';
require_once 'db.php';

$url = 'http://klan.com.ua';
$log = 'log.txt';

$db = DB::getInstance();
//$logHandle = fopen($log, 'w+b');
$modelsCount = 0;
$modificationsCount = 0;
$productsCount = 0;
$attributesCount = 0;

$html = file_get_contents_repeat($url);
phpQuery::newDocument($html);
$brands = pq('.klan-brands-block')->children('.item');

foreach ($brands as $brandIndex => $brand) {
  $brandReferenceTag = pq($brand)->children('a');
  $brandName = trim($brandReferenceTag->text());
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//  fwrite($logHandle, PHP_EOL . PHP_EOL . 'BRAND: ' . $brandName . PHP_EOL . PHP_EOL);

//  echo 'BRAND: ' . trim($brandReferenceTag->text()) . PHP_EOL;
  $db->save('car_brands', [
    'id'    => $brandIndex,
    'name'  => $brandName
  ]);

  $modelGroupsUrl = $url . $brandReferenceTag->attr('href');
//  echo $modelGroupsUrl . PHP_EOL;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//  fwrite($logHandle, 'MODEL_GROUPS: ' . $modelGroupsUrl . PHP_EOL);
  $modelGroupsHTML = phpQuery::newDocument(file_get_contents_repeat($modelGroupsUrl));
  $modelGroups = $modelGroupsHTML->find('form#table_form > table.row_model_group_table')->children('tr[id*="tr"]');

  foreach ($modelGroups as $modelGroupIndex => $modelGroup) {
    $modelGroupCell = pq($modelGroup)->children('td');
    $modelReferenceTag = $modelGroupCell->eq(1)->children('a');
    $modelsUrl = $url . $modelReferenceTag->attr('href');
//    echo $modelsUrl . PHP_EOL;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//    fwrite($logHandle, 'MODELS: ' . $modelsUrl . PHP_EOL);
    $modelsHTML = phpQuery::newDocument(file_get_contents_repeat($modelsUrl/* . '/?data[is_actual]=0' */));
    $models = $modelsHTML->find('form#table_form > table.row_model_table')->children('tr[id*="tr"]');

    foreach ($models as $modelIndex => $model) {
      $modelCell = pq($model)->children('td');
      $model_years = getYears($modelCell->eq(2)->text());
      $modificationReferenceTag = $modelCell->eq(1)->children('a');
      $modelImageReference = $modelCell->eq(0)->children('a')->children('img')->attr('src');

//      echo 'MODEL: ' . trim($modificationReferenceTag->text()) . PHP_EOL;
      $db->save('car_models', [
        'id'                    => $modelsCount + $modelIndex,
        'name'                  => trim($modificationReferenceTag->text()),
        'image_src'             => $modelImageReference ? ($url . $modelImageReference) : '',
        'year_manufacture'      => $model_years[0],
        'year_end_manufacture'  => $model_years[1],
        'brand_id'              => $brandIndex
      ]);

      $modificationsUrl = $url . $modificationReferenceTag->attr('href');
//      echo $modificationsUrl;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//      fwrite($logHandle, 'MODIFICATIONS: ' . $modificationsUrl . PHP_EOL);
      $modificationsHTML = phpQuery::newDocument(file_get_contents_repeat($modificationsUrl));
      $modifications = $modificationsHTML->find('form#table_form > table.row_modeldetail_table')->children('tr[id*="tr"]');

      foreach ($modifications as $modificationIndex => $modification) {
        $modificationCell = pq($modification)->children('td');
        $modification_years = getYears($modificationCell->eq(1)->text());
        $categoriesReferenceTag = $modificationCell->eq(0)->children('a');

//        echo 'MODIFICATION: ' . trim($categoriesReferenceTag->text()) . PHP_EOL;
        $db->save('car_modifications', [
          'id'                    => $modificationsCount + $modificationIndex,
          'name'                  => trim($categoriesReferenceTag->text()),
          'car_model_id'          => $modelsCount + $modelIndex,
          'year_manufacture'      => $modification_years[0],
          'year_end_manufacture'  => $modification_years[1]
        ]);

        $categoriesUrl = $url . $categoriesReferenceTag->attr('href');
//        echo $categoriesUrl . PHP_EOL;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//        fwrite($logHandle, 'CATEGORIES: ' . $categoriesUrl . PHP_EOL);
        $categoriesHTML = phpQuery::newDocument(file_get_contents_repeat($categoriesUrl));
        $categories = getCategories($categoriesHTML->
          find('body > div.page-container > div.page-wrap > section div.all-table div.left-table > div.dtree_hd + script')->text());
        $modificationImageReference = $categoriesHTML->find('.all-table > .right-table > .image-cars > img:first')->attr('src');

        foreach ($categories as $category) {
//          echo 'CATEGORY: ' . $category[2] . PHP_EOL;
          $db->save('car_product_categories', [
            'id'        => $category[0],
            'parent_id' => $category[1],
            'name'      => str_replace('\'', '', $category[2])
          ]);


          $db->update('car_modifications', [
            'id' => $modificationsCount + $modificationIndex,
            'image_src' => $modificationImageReference ? ($url . $modificationImageReference) : ''
          ]);

//          echo $category[3] . PHP_EOL;
          $productsUrl = $url . str_replace('\'', '', $category[3]);
//          echo $productsUrl;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//          fwrite($logHandle, 'PRODUCTS: ' . $productsUrl . PHP_EOL);
          $productsHTML = phpQuery::newDocument(file_get_contents_repeat($productsUrl));
          $products = $productsHTML->find('form#table_form > table.row_part_table')->children('tr[id*="tr"');

          foreach ($products as $productIndex => $product) {
            $productCell = pq($product)->children('td');
            $attributesReferenceTag = $productCell->eq(0)->children('a');
            $productImageReference = $productCell->eq(1)->children('a')->children('img')->attr('src');

//            echo 'PRODUCT: ' . trim($productCell->eq(0)->text()) . PHP_EOL;
            $db->save('car_products', [
              'id'            => $productsCount + $productIndex,
              'name'          => trim($attributesReferenceTag->text()),
              'image_src'     => $productImageReference ? ($url . $productImageReference) : '',
              'price'         => $productCell->eq(4)->children('nobr')->text(),
              'quantity'      => intval($productCell->eq(3)->text()),
              'delivery_time' => str_replace('---', '', trim($productCell->eq(2)->text())),
//              'vendor_code'   => trim($productCell->eq(0)->text()),
              'category_id'   => $category[0]
            ]);

            $db->save('car_product_to_model', [
              'id'              => $productsCount + $productIndex,
              'car_product_id'  => $productsCount + $productIndex,
              'model_id'        => $modelsCount + $modelIndex,
              'modification_id' => $modificationsCount + $modificationIndex
            ]);

            $db->save('car_product_to_category', [
              'id'          => $productsCount + $productIndex,
              'id_product'  => $productsCount + $productIndex,
              'id_category' => $category[0]
            ]);

            $attributesUrl = $url . $attributesReferenceTag->attr('href');
//            echo $attributesUrl . PHP_EOL;
//------------------------------------------------------------ ЗАПИСЬ В ЛОГ ФАЙЛ -------------------------------------------------------------
//            fwrite($logHandle, 'ATTRIBUTES: ' . $attributesUrl . PHP_EOL);
            $attributesContent = file_get_contents_repeat($attributesUrl);

            if ($attributesContent) {
              $attributesHTML = phpQuery::newDocument($attributesContent);
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

//                    echo 'ATTRIBUTE: ' . trim($attribute) . PHP_EOL;
                    $db->save('car_product_attributes', [
                      'id'    => $attributeId,
                      'name'  => $attributes[0][$attributeIndex]
                    ]);
                  }

                  echo '.';
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
          echo ' ' . $productsCount . ' ';
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
}
phpQuery::unloadDocuments();
//fclose($logHandle);
//phpQuery::unloadDocuments($html);
