USE carDetails;
DROP TABLE car_product_attr_to_product;
DROP TABLE car_product_attributes;
DROP TABLE car_product_to_category;
DROP TABLE car_product_to_model;
DROP TABLE car_products;
DROP TABLE car_product_categories;
DROP TABLE car_modifications;
DROP TABLE car_models;
DROP TABLE car_brands;

USE carDetails;
CREATE TABLE car_brands
(
  id INT PRIMARY KEY NOT NULL,
  name NVARCHAR(25),
  image_src NVARCHAR(255)
);
CREATE TABLE car_models
(
  id INT PRIMARY KEY NOT NULL,
  name NVARCHAR(500),
  image_src VARCHAR(255),
  year_manufacture NVARCHAR(50),
  year_end_manufacture NVARCHAR(50),
  brand_id INT,
  CONSTRAINT car_models_car_brands_id_fk FOREIGN KEY (brand_id) REFERENCES car_brands (id)
);
CREATE TABLE car_modifications
(
  id INT PRIMARY KEY NOT NULL,
  name NVARCHAR(500),
  car_model_id INT,
  image_src VARCHAR(255),
  year_manufacture NVARCHAR(50),
  year_end_manufacture NVARCHAR(50),
  CONSTRAINT car_modifications_car_models_id_fk FOREIGN KEY (car_model_id) REFERENCES car_models (id)
);
CREATE TABLE car_product_categories
(
  id INT PRIMARY KEY NOT NULL,
  parent_id INT DEFAULT 0,
  name NVARCHAR(255)
);
CREATE TABLE car_products
(
  id INT PRIMARY KEY NOT NULL,
  name NVARCHAR(500),
  image_src VARCHAR(255),
  price DECIMAL,
  quantity INT,
  delivery_time VARCHAR(15),
  vendor VARCHAR(50),
  vendor_code VARCHAR(50),
  description NVARCHAR(2000),
  category_id INT,
  is_updated BIT DEFAULT 0,
  CONSTRAINT car_products_car_product_categories_id_fk FOREIGN KEY (category_id) REFERENCES car_product_categories (id)
);
CREATE TABLE car_product_to_model
(
  id INT PRIMARY KEY NOT NULL,
  car_product_id INT,
  model_id INT,
  modification_id INT,
  CONSTRAINT car_product_to_model_car_products_id_fk FOREIGN KEY (car_product_id) REFERENCES car_products (id),
  CONSTRAINT car_product_to_model_car_models_id_fk FOREIGN KEY (model_id) REFERENCES car_models (id),
  CONSTRAINT car_product_to_model_car_modifications_id_fk FOREIGN KEY (modification_id) REFERENCES car_modifications (id)
);
CREATE TABLE car_product_to_category
(
  id INT PRIMARY KEY NOT NULL,
  product_id INT,
  category_id INT,
  CONSTRAINT car_product_to_category_car_products_id_fk FOREIGN KEY (product_id) REFERENCES car_products (id),
  CONSTRAINT car_product_to_category_car_product_categories_id_fk FOREIGN KEY (category_id) REFERENCES car_product_categories (id)
);
CREATE TABLE car_product_attributes
(
  id INT PRIMARY KEY NOT NULL,
  name NVARCHAR(255)
);
CREATE TABLE car_product_attr_to_product
(
  id INT PRIMARY KEY NOT NULL,
  product_id INT,
  attr_id INT,
  value NVARCHAR(500),
  CONSTRAINT car_product_attr_to_product_car_products_id_fk FOREIGN KEY (product_id) REFERENCES car_products (id),
  CONSTRAINT car_product_attr_to_product_car_product_attributes_id_fk FOREIGN KEY (attr_id) REFERENCES car_product_attributes (id)
);


SELECT car_models.name, car_brands.name
FROM car_models
  LEFT JOIN car_brands
    ON car_models.brand_id = car_brands.id;

SELECT id FROM car_product_attributes WHERE name='Спецификация';

SELECT vendor FROM car_products WHERE vendor_code='CT1047';

SELECT vendor_code FROM carDetails.car_products WHERE id=1;
SELECT vendor FROM carDetails.car_products WHERE vendor_code='NJ242';
SELECT * FROM carDetails.car_products WHERE (vendor_code='850722');
