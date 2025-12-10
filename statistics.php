<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exoplanet Website</title>
    <link rel="icon" href="images/planet_tab_image.png" type="image/png">
    <!-- This is needed for the Rubik font to work, from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- This is needed for icons, hosted by Font Awesome -->
    <script src="https://kit.fontawesome.com/68f1cdffaa.js" crossorigin="anonymous"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100%;
            width: 100%;
        }

        #legend {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.65);
            color: white;
            font-family: Rubik, Arial, sans-serif;
            font-size: 14px;
            border-radius: 8px;
            z-index: 9999; /* stays on top of WebGL */
        }

        #legend h3 {
            margin: 0 0 8px 0;
            font-size: 15px;
        }

        .color-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            border: 1px solid #ccc;
        }

    </style>
</head>
<body>
    <section id="statistics">
        <?php
        // These are the connection details for the database
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'exoplanet_database';

        // Connection to the database
        $conn = new mysqli($host, $user, $password, $database);
        if ($conn->connect_error) {
            die("Unable to connect to database: " . $conn->connect_error);
        }

        // This will be filled with the data from all the planets queried
        $planets = [];

        /* Test: just print out whatever values the user gave in query-planets.php */
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $min_dist = isset($_POST["min_dist"]) ? $_POST["min_dist"] : 0;
            $max_dist = isset($_POST["max_dist"]) ? $_POST["max_dist"] : 10000;
            $min_orbit = isset($_POST["min_orbit"]) ? $_POST["min_orbit"] : 0;
            $max_orbit = isset($_POST["max_orbit"]) ? $_POST["max_orbit"] : 19000;
            $min_year = isset($_POST["min_year"]) ? $_POST["min_year"] : 1992;
            $max_year = isset($_POST["max_year"]) ? $_POST["max_year"] : 2025;
            $methods = isset($_POST["discovery_method"])  ? $_POST["discovery_method"]  : [];
            $methods_list = '';
            for ($method = 0; $method < count($methods); $method++) {
                if ($method === count($methods) - 1) {
                    $methods_list .= "'" . $methods[$method] . "'";
                } else {
                    $methods_list .= "'" . $methods[$method] . "'". ",";
                }
            }

            // This generates the list of planets that the user has requested to be displayed (requires at least one discovery method selected)
            if (!empty($methods_list)) {
                $planets_query = "SELECT p.planet_id AS planet_id, 
                                    p.orbital_distance AS orbital_distance, 
                                    dm.name AS discovery_method,
                                    dd.discovery_year AS discovery_year, 
                                    s.star_distance AS distance, 
                                    s.right_ascension AS right_ascension, 
                                    s.declination AS declination 
                                    FROM Planets p 
                                    JOIN Discovery_Details dd ON dd.planet_id = p.planet_id 
                                    JOIN Discovery_Methods dm ON dm.method_id = dd.method_id 
                                    JOIN Stars s ON s.star_id = p.star_id 
                                    WHERE p.orbital_distance BETWEEN $min_orbit AND $max_orbit 
                                      AND dd.discovery_year BETWEEN $min_year AND $max_year 
                                      AND s.star_distance BETWEEN $min_dist AND $max_dist 
                                      AND dm.name IN ($methods_list)";
                $planets_query_result = mysqli_query($conn, $planets_query);
                while ($planet_row = mysqli_fetch_assoc($planets_query_result)) {
                    $planets[] = $planet_row;
                }
            } else {
                echo '<p>No discovery methods were selected or no planets were found with the given criteria. Please fill out the form again <a href="query-planets.php">here</a> and select at least one discovery method.</p>';
            }
        } else {
            echo '<p>Please submit the form located under Query Planets. You can find the form <a href="query-planets.php">here</a>.</p>';
        }
        ?>
    </section>
    <?php if (count($planets) > 0): ?>
        <?php
        // This section is used to construct the map legend
        $used_methods = [];
        foreach ($planets as $p) {
            $used_methods[$p['discovery_method']] = true;
        }

        $method_colors = [
                'Transit' => '#ff0000',
                'Radial Velocity' => '#00ff00',
                'Microlensing' => '#00ffff',
                'Imaging' => '#ffff00'
        ];
        ?>
        <div id="legend">
            <h3>Planet Discovery Methods</h3>
                <?php foreach ($method_colors as $method => $color): ?>
                    <?php if (isset($used_methods[$method])): ?>
                        <div>
                            <span class="color-box" style="background: <?= $color ?>;"></span>
                            <?= htmlspecialchars($method) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
        </div>
        <section id="map">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
            <script src="https://threejs.org/examples/jsm/controls/OrbitControls.js"></script>
            <script type="module">
                import * as THREE from "https://cdn.jsdelivr.net/npm/three@0.121.1/build/three.module.js";
                import { OrbitControls } from "https://cdn.jsdelivr.net/npm/three@0.121.1/examples/jsm/controls/OrbitControls.js";
                <?php
                    echo "let planets = " . json_encode($planets) . ";";
                ?>
                // Create the scene
                const scene = new THREE.Scene();

                // This is for the camera
                let camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 1, 10000);
                camera.position.set(0, 0, 1000);

                // This sets up the renderer
                const renderer = new THREE.WebGLRenderer({ antialias: true });
                renderer.setSize(window.innerWidth, window.innerHeight);
                document.body.appendChild(renderer.domElement);

                // This sets up the controls
                const controls = new OrbitControls(camera, renderer.domElement);
                const raycaster = new THREE.Raycaster();
                const mouse = new THREE.Vector2();
                const mouse3D = new THREE.Vector3();

                // These store the planet positions and colors for each planet
                const planet_positions = [];
                const planet_colors = [];

                // This converts the right ascension, declination, and distance of a particular planet to Cartesian Coordinates
                function toCartesian(ra, dec, dist) {
                    const ra_rad = ra * (Math.PI / 180);
                    const dec_rad = dec * (Math.PI / 180);

                    const x = dist * Math.cos(dec_rad) * Math.cos(ra_rad);
                    const y = dist * Math.cos(dec_rad) * Math.sin(ra_rad);
                    const z = dist * Math.sin(dec_rad);

                    return {x, y, z}
                }

                // This returns the color for each planet depending on its discovery method
                function toColor(method) {
                    // These define the color of each planet, depending on the discovery method used for it
                    const colors = {
                        'Transit': new THREE.Color(0xff0000), // RED
                        'Radial Velocity': new THREE.Color(0x00ff00), // GREEN
                        'Microlensing': new THREE.Color(0x00ffff), // BLUE
                        'Imaging': new THREE.Color(0xffff00) // YELLOW
                    }

                    return colors[method];
                }

                // This is what processes the planet data and actually makes them all appear in the scene
                planets.forEach(planet => {
                   let coords = toCartesian(parseFloat(planet.right_ascension),
                       parseFloat(planet.declination),
                       parseFloat(planet.distance)
                   );

                    planet_positions.push(coords.x, coords.y, coords.z);

                    let color = toColor(planet.discovery_method);
                    planet_colors.push(color.r, color.g, color.b);
                });

                // This creates the mesh in which the planets actually reside
                const geometry = new THREE.BufferGeometry();
                geometry.setAttribute("position", new THREE.Float32BufferAttribute(planet_positions, 3));
                geometry.setAttribute("color", new THREE.Float32BufferAttribute(planet_colors, 3));

                const material = new THREE.PointsMaterial({
                    size: 2.0, // The size of each particle
                    vertexColors: true, // This specifies that we want a color per point (planet)
                })

                const particles = new THREE.Points(geometry, material);
                scene.add(particles);

                // This is the animation loop
                function animate() {
                    requestAnimationFrame(animate);

                    controls.update();
                    renderer.render(scene, camera);
                }

                animate();

                // This handles cases in which the window may be resized
                window.addEventListener('resize', () => {
                    camera.aspect = window.innerWidth / window.innerHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(window.innerWidth, window.innerHeight);
                });
            </script>
        </section>
    <?php endif; ?>
</body>
</html>
