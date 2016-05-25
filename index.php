<?php
require_once 'vendor/autoload.php';

$url = 'http://klan.com.ua';
$log = 'log.txt';

$db = DB::getInstance();
$curl = new Curl();
$logHandle = fopen($log, 'w+b');
$modelsCount = 0;
$modificationsCount = 0;
$productsCount = 0;
$attributesCount = 0;

$uniqueAttributesCount = 0;

$html = $curl->set($url);
phpQuery::newDocument($curl->getContent($html));

//$brands = pq('.klan-brands-block > .item');
$brands = pq('div.item');

foreach ($brands as $brandIndex => $brand) {

  $brandReferenceTag  = pq($brand)->children('a');
  $modelGroupsConnection  = $curl->set($url . $brandReferenceTag->attr('href'));
  $modelGroupsHTML        = phpQuery::newDocument($curl->getContent($modelGroupsConnection));
  $modelGroups            = $modelGroupsHTML->find('.datatable')->children('tr[id*="tr"]');

  $db->save('car_brands', [
    'id'    => $brandIndex,
    'name'  => trim($brandReferenceTag->text()),
    'image_src' => url($url, $brandReferenceTag->children('img')->attr('src'))
//    'image_src' => $url . $brandReferenceTag->children('img')->attr('src')
  ]);

  foreach ($modelGroups as $modelGroupIndex => $modelGroup) {

    $modelGroupCell     = pq($modelGroup)->children('td');
    $modelReferenceTag  = $modelGroupCell->eq(1)->children('a');
    $modelsConnection   = $curl->set($url . $modelReferenceTag->attr('href')/* . '/?data[is_actual]=0' */);
    $modelsHTML         = phpQuery::newDocument($curl->getContent($modelsConnection));
    $models             = $modelsHTML->find('.datatable')->children('tr[id*="tr"]');

    foreach ($models as $modelIndex => $model) {
      $modelCell                = pq($model)->children('td');
      $modificationReferenceTag = $modelCell->eq(1)->children('a');
      $modificationsConnection  = $curl->set($url . $modificationReferenceTag->attr('href'));
      $modificationsHTML        = phpQuery::newDocument($curl->getContent($modificationsConnection));
      $modifications            = $modificationsHTML->find('.datatable')->children('tr[id*="tr"]');

      $model_years              = getYears($modelCell->eq(2)->text());
      $db->save('car_models', [
        'id'                    => $modelsCount + $modelIndex,
        'name'                  => trim($modificationReferenceTag->text()),
        'image_src'             => url($url, $modelCell->eq(0)->find('img')->attr('src')),
//        'image_src'             => $url . $modelCell->eq(0)->find('img')->attr('src'),
        'year_manufacture'      => $model_years[0],
        'year_end_manufacture'  => $model_years[1],
        'brand_id'              => $brandIndex
      ]);

      foreach ($modifications as $modificationIndex => $modification) {
        $modificationCell       = pq($modification)->children('td');
        $categoriesReferenceTag = $modificationCell->eq(0)->children('a');
        $categoriesConnection   = $curl->set($url . $categoriesReferenceTag->attr('href'));
        $categoriesHTML         = phpQuery::newDocument($curl->getContent($categoriesConnection));
        $categories             = getCategories($categoriesHTML->find('.dtree_hd + script')->text());
        $modificationImageReference = $categoriesHTML->find('.image-cars > img:first')->attr('src');

        $modification_years     = getYears($modificationCell->eq(1)->text());
        $db->save('car_modifications', [
          'id'                    => $modificationsCount + $modificationIndex,
          'name'                  => trim($categoriesReferenceTag->text()),
          'car_model_id'          => $modelsCount + $modelIndex,
          'year_manufacture'      => $modification_years[0],
          'year_end_manufacture'  => $modification_years[1]
        ]);

        foreach ($categories as $category) {
          $productsConnection = $curl->set($url . str_replace('\'', '', $category[3]));
          $productsHTML       = phpQuery::newDocument($curl->getContent($productsConnection));
          $products           = $productsHTML->find('.datatable')->children('tr[id*="tr"');

          $db->save('car_product_categories', [
            'id'        => $category[0],
            'parent_id' => $category[1],
            'name'      => str_replace('\'', '', $category[2])
          ]);

          $db->update('car_modifications', [
            'id'        => $modificationsCount + $modificationIndex,
            'image_src' => url($url, $modificationImageReference)
//            'image_src' => $url . $modificationImageReference
          ]);

          foreach ($products as $productIndex => $product) {
            $productCell            = pq($product)->children('td');
            $attributesReferenceTag = $productCell->eq(0)->children('a');
            $attributesConnection   = $curl->set($url . $attributesReferenceTag->attr('href'));
//            $productImageReference  = $productCell->eq(1)->children('a')->children('img')->attr('src');
            $productImageReference  = $productCell->eq(1)->find('img')->attr('src');

            $vendorAttributes = explode(' ', explode('br>', $productCell->eq(0)->html())[1]);
            $db->save('car_products', [
              'id'            => $productsCount + $productIndex,
              'name'          => trim($attributesReferenceTag->text()),
              'image_src'     => url($url, $productImageReference),
//              'image_src'     => $productImageReference,
              'price'         => floatval($productCell->eq(4)->children('nobr')->text()),
              'quantity'      => intval($productCell->eq(3)->text()),
              'delivery_time' => str_replace('---', '', trim($productCell->eq(2)->text())),
              'vendor'        => trim($vendorAttributes[0]),
              'vendor_code'   => trim($vendorAttributes[1]),
//              'description'   =>
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
              'product_id'  => $productsCount + $productIndex,
              'category_id' => $category[0]
            ]);

//            if ($attributesConnection) {
//              $attributesHTML = phpQuery::newDocument($curl->getContent($attributesConnection));
//              $attributesCell = $attributesHTML->find('.p-info > span:first');
//
//              $attributesRows = $attributesCell->find('tr');
//              $attributes     = getAttributes($attributesRows->text());
//              $attributesRows->remove();
//              $attributeVendor = getVendor($attributesCell->text());
//
//              $db->update('car_products', [
//                'id'          => $productsCount + $productIndex,
////                'name'        => trim($attributesHTML->find('.cat-5-content > [itemprop*="name"]')->text()),
////                'image_src'   => $attributesHTML->find('.p-images > a')->children('img')->attr('src'),
//                'vendor'      => $attributeVendor,
//                'vendor_code' => trim(explode(':', $attributesHTML->find('.p-code')->text())[1]),
//                'description' => $attributesHTML->find('.p-description'_)->text()
//              ]);
//
//              if ($attributes) {
//                foreach ($attributes[0] as $attributeIndex => $attribute) {
//                  $uniqueAttributeIndex = $uniqueAttributesCount;
//                  $uniqueAttribute      = $db->select('car_product_attributes', ['id'], ['name' => $attribute]);
//
//                  if ($uniqueAttribute) {
//                    $uniqueAttributeIndex = $uniqueAttribute[0]['id'];
//                  } else {
//                    $db->insert('car_product_attributes', [
//                      'id'    => $uniqueAttributesCount,
//                      'name'  => $attribute
//                    ]);
//                    ++$uniqueAttributesCount;
//                  }
//
//                  echo '.';
//                  $db->save('car_product_attr_to_product', [
//                    'id'          => $attributesCount + $attributeIndex,
//                    'product_id'  => $productsCount + $productIndex,
//                    'attr_id'     => $uniqueAttributeIndex,
//                    'value'       => $attributes[1][$attributeIndex]
//                  ]);
//                }
//                $attributesCount += count($attributes[0]);
//              }
//              $curl->close($attributesConnection);
//              phpQuery::unloadDocuments($attributesHTML);
//            }
          }
          $curl->close($productsConnection);
          phpQuery::unloadDocuments($productsHTML);
          $productsCount += count($products);
          echo ' ' . $productsCount . ' ';
        }
        $curl->close($categoriesConnection);
        phpQuery::unloadDocuments($categoriesHTML);
      }
      $curl->close($modificationsConnection);
      phpQuery::unloadDocuments($modificationsHTML);
      $modificationsCount += count($modifications);
    }
    $curl->close($modelsConnection);
    phpQuery::unloadDocuments($modelsHTML);
    $modelsCount += count($models);
  }
  $curl->close($modelGroupsConnection);
  phpQuery::unloadDocuments($modelGroupsHTML);
}
$curl->close($html);
phpQuery::unloadDocuments($html);
