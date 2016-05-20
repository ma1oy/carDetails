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
  name VARCHAR(50)
);
CREATE TABLE car_models
(
  id INT PRIMARY KEY NOT NULL,
  name VARCHAR(100),
  image_src VARCHAR(255),
  year_manufacture VARCHAR(10),
  year_end_manufacture VARCHAR(10),
  brand_id INT,
  CONSTRAINT car_models_car_brands_id_fk FOREIGN KEY (brand_id) REFERENCES car_brands (id)
);
CREATE TABLE car_modifications
(
  id INT PRIMARY KEY NOT NULL,
  name VARCHAR(100),
  car_model_id INT,
  parent_id INT,
  image_src VARCHAR(255),
  year_manufacture VARCHAR(10),
  year_end_manufacture VARCHAR(10),
  CONSTRAINT car_modifications_car_models_id_fk FOREIGN KEY (car_model_id) REFERENCES car_models (id)
);
CREATE TABLE car_product_categories
(
  id INT PRIMARY KEY NOT NULL,
  parent_id INT DEFAULT 0,
  name VARCHAR(255)
);
CREATE TABLE car_products
(
  id INT PRIMARY KEY NOT NULL,
  name VARCHAR(100),
  image_src VARCHAR(255),
  price VARCHAR(50),
  quantity INT,
  delivery_time VARCHAR(50),
  vendor VARCHAR(50),
  vendor_code VARCHAR(50),
  category_id INT,
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
  id_product INT,
  id_category INT,
  CONSTRAINT car_product_to_category_car_products_id_fk FOREIGN KEY (id_product) REFERENCES car_products (id),
  CONSTRAINT car_product_to_category_car_product_categories_id_fk FOREIGN KEY (id_category) REFERENCES car_product_categories (id)
);
CREATE TABLE car_product_attributes
(
  id INT PRIMARY KEY NOT NULL,
  name VARCHAR(100)
);
CREATE TABLE car_product_attr_to_product
(
  id INT PRIMARY KEY NOT NULL,
  id_product INT,
  id_attr INT,
  value VARCHAR(50),
  CONSTRAINT car_product_attr_to_product_car_products_id_fk FOREIGN KEY (id_product) REFERENCES car_products (id),
  CONSTRAINT car_product_attr_to_product_car_product_attributes_id_fk FOREIGN KEY (id_attr) REFERENCES car_product_attributes (id)
);
