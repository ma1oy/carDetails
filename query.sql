USE carDetails;
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
  name VARCHAR(50),
  image_src VARCHAR(255),
  year_manufacture VARCHAR(10),
  year_end_manufacture VARCHAR(10),
  brand_id INT,
  CONSTRAINT car_models_car_brands_id_fk FOREIGN KEY (brand_id) REFERENCES car_brands (id)
);
CREATE TABLE carDetails.car_modifications
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


SELECT car_models.name, car_brands.name
FROM car_models
  LEFT JOIN car_brands
    ON car_models.brand_id = car_brands.id;

