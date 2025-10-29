# Exoplanet-Website
This is the repository for my Database-Backed Web Services Course.

### Attributions

Tab planet image attribution: <a href="https://www.flaticon.com/free-icons/planet" title="planet icons">Planet icons created by monkik - Flaticon</a>

Sidebar planet image attribution: <a href="https://www.flaticon.com/free-icons/planet" title="planet icons">Planet icons created by Nsit - Flaticon</a>

### Database (initial) Schema

```
Reference {
	reference_id INT IDENTITY PRIMARY KEY,
	reference_name VARCHAR(255)
}
```

```
Discovery_Method {
	discovery_method_id INT IDENTITY PRIMARY KEY,
	method_name VARCHAR(255) UNIQUE
}
```

```
Planet {
	planet_id INT IDENTITY PRIMARY KEY,
	planet_name VARCHAR(255),
	discovery_method_id INT,
	discovery_year INT,
	star_id INT,
	FOREIGN KEY (star_id) REFERENCES Star(star_id),
	FOREIGN KEY (discovery_method_id) REFERENCES Discovery_Method(discovery_method_id)
}
```

```
Star {
	star_id INT PRIMARY KEY,
	star_name VARCHAR(255)
}
```

```
Planet_Properties {
	planet_id INT,
	reference_id INT,
	planet_mass FLOAT,
	PRIMARY KEY (planet_id, reference_id),
	FOREIGN KEY (planet_id) REFERENCES Planet(planet_id),
	FOREIGN KEY (reference_id) REFERENCES Reference(reference_id)
}
```

```
Star_Properties {
	star_id INT,
	reference_id INT,
	star_mass FLOAT,
	star_radius FLOAT,	
	PRIMARY KEY(star_id, reference_id),
	FOREIGN KEY (star_id) REFERENCES Star(star_id),
	FOREIGN KEY (reference_id) REFERENCES Reference(reference_id)
}
```

```
Star_Location {
	star_id INT,
	reference_id INT,
	right_ascension FLOAT,
	declination FLOAT,
	distance FLOAT,
	x FLOAT,
	y FLOAT,
	z FLOAT,
	
	PRIMARY KEY(star_id, reference_id),
	FOREIGN KEY (star_id) REFERENCES Star(star_id),
	FOREIGN KEY (reference_id) REFERENCES Reference(reference_id)
}
```

