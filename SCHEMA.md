# Database Schema

This is the Schema for my database used by my Exoplanet Search website.

## Stars

Below is the query I ran to create the Stars table in the database:
```
CREATE TABLE Stars (
    star_id INT PRIMARY KEY AUTO_INCREMENT,
    star_name VARCHAR(255) NOT NULL,
    star_radius DOUBLE,
    star_mass DOUBLE,
    right_ascension DOUBLE,
    declination DOUBLE,
    star_distance DOUBLE,
    num_planets INT
);
```

Below is the query I ran to insert the data from the `raw_data_table` into the `Stars` table:

```
INSERT INTO Stars (star_name, star_radius, star_mass, right_ascension, declination, star_distance, num_planets)
SELECT DISTINCT
       hostname,
       CAST(st_rad AS DOUBLE),
       CAST(st_mass AS DOUBLE),
       ra,
       `dec`,
       CAST(sy_dist AS DOUBLE),
       sy_pnum
FROM raw_data_table;
```

## Planets

Below is the query I ran to create the Planets table in the database:

```
CREATE TABLE Planets (
    planet_id INT AUTO_INCREMENT PRIMARY KEY,
    planet_name VARCHAR(255) NOT NULL,
    star_id INT NOT NULL,
    orbital_period DOUBLE,
    orbital_distance DOUBLE,
    planet_radius_earths DOUBLE,
    planet_mass_earths DOUBLE,
    
    FOREIGN KEY (star_id) REFERENCES Stars(star_id) ON DELETE CASCADE
);
```

## Discovery_Methods

Below is the query I ran to create the Discovery_Methods table in the database:

```
CREATE TABLE Discovery_Methods (
    method_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
```

## Discovery_Details

Below is the query I ran to create the Discovery_Details table in the database:

```
CREATE TABLE Discovery_Details (
    planet_id INT PRIMARY KEY,
    method_id INT NOT NULL,
    discovery_year INT NOT NULL,
    discovery_facility VARCHAR(255),
        
    FOREIGN KEY (planet_id) REFERENCES Planets(planet_id) ON DELETE CASCADE,
    FOREIGN KEY (method_id) REFERENCES Discovery_Methods(method_id) ON DELETE CASCADE
);
```

